<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\LeaveApprovalWorkflow;
use App\Models\LeaveRequestApproval;
use App\Models\OfficeSetting;
use App\Models\User;
use App\Models\UserAnnualLeaveEntitlement;
use App\Services\MenuAccessService;
use App\Services\XlsxFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class EmployeeSpreadsheetController extends Controller
{
    private const HEADERS = [
        'id', 'name', 'username', 'email', 'nik', 'position', 'department',
        'approval_level', 'office', 'role', 'is_active', 'annual_leave_entitlement', 'password',
    ];

    private const TEMPLATE_HEADERS = [
        'name', 'nik', 'position', 'department', 'approval_level', 'office', 'role', 'password',
    ];

    public function export()
    {
        $year = (int) now()->year;
        $users = User::query()
            ->where('role', '!=', 'superadmin')
            ->with(['department', 'office'])
            ->orderBy('id')
            ->get();
        $entitlements = UserAnnualLeaveEntitlement::query()
            ->where('year', $year)
            ->whereIn('user_id', $users->modelKeys())
            ->pluck('days', 'user_id');
        $rows = $users->map(fn (User $user) => [
            $user->id,
            $user->name,
            $user->username,
            $user->email,
            $user->nik,
            $user->position,
            $user->department?->name,
            $user->approval_level,
            $user->office?->name,
            $user->role,
            $user->is_active ? 1 : 0,
            $entitlements[$user->id] ?? 12,
            '',
        ]);

        return XlsxFile::download('data-karyawan.xlsx', self::HEADERS, $rows);
    }

    public function template()
    {
        $departments = Department::query()->orderBy('name')->pluck('name')->all();
        $approvalLevels = LeaveApprovalWorkflow::LEVELS;
        $offices = OfficeSetting::query()->orderBy('name')->pluck('name')->all();
        $roles = app(MenuAccessService::class)->managedRoles();
        $guide = [
            ['name', 'Wajib diisi. Nama lengkap karyawan. Username dibuat otomatis dari nama depan dan dua angka acak, sama seperti saat menambah pengguna melalui website.'],
            ['nik', 'Opsional. NIK karyawan dan harus unik jika diisi.'],
            ['position', 'Opsional. Jabatan karyawan.'],
            ['department', 'Opsional. Pilih nama departemen dari daftar pada sheet Referensi.'],
            ['approval_level', 'Opsional. Pilih tingkat approval dari daftar pada sheet Referensi. Jika diisi, departemen wajib dipilih. Tingkat SPBU/LPG juga memerlukan lokasi kerja yang sesuai.'],
            ['office', 'Opsional. Pilih lokasi kerja dari daftar pada sheet Referensi. Wajib untuk tingkat approval SPBU/LPG.'],
            ['role', 'Pilih role dari daftar pada sheet Referensi. Jika dikosongkan, role Karyawan digunakan.'],
            ['password', 'Wajib diisi. Gunakan kata sandi awal yang kuat dan sampaikan kepada karyawan.'],
            ['Pengaturan otomatis', 'Username dan ID dibuat otomatis. Akun aktif dan jatah cuti tahunan 12 hari.'],
            ['Cara impor', 'Baris 2 adalah contoh: ganti atau hapus sebelum impor agar akun contoh tidak dibuat. Isi satu baris untuk setiap karyawan. Gunakan dropdown pada kolom departemen, approval_level, office, dan role untuk menghindari salah input. Simpan sebagai .xlsx, lalu unggah melalui menu Superadmin > Pengguna > Impor Excel. Maksimal 5.000 baris dan 10 MB.'],
        ];
        $referenceHeaders = ['Departemen', 'Tingkat approval', 'Lokasi kerja', 'Role pengguna'];
        $referenceColumns = [$departments, array_values($approvalLevels), $offices, array_values($roles)];
        $referenceRowCount = max(array_map('count', $referenceColumns));
        $referenceRows = [];
        for ($index = 0; $index < $referenceRowCount; $index++) {
            $referenceRows[] = array_map(fn (array $values) => $values[$index] ?? '', $referenceColumns);
        }
        $example = [[
            'CONTOH - HAPUS SEBELUM IMPOR',
            '',
            'Contoh Jabatan',
            '',
            '',
            '',
            'Karyawan',
            'GantiPassword123!',
        ]];

        return XlsxFile::download(
            'template-data-karyawan.xlsx',
            self::TEMPLATE_HEADERS,
            $example,
            $guide,
            $referenceHeaders,
            $referenceRows,
            ['D' => 'DepartmentOptions', 'E' => 'ApprovalLevelOptions', 'F' => 'OfficeOptions', 'G' => 'RoleOptions'],
        );
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx', 'max:10240'],
        ]);

        try {
            $rows = XlsxFile::read($request->file('file'));
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['file' => $exception instanceof RuntimeException
                ? $exception->getMessage()
                : 'File Excel tidak dapat dibaca. Pastikan file berformat .xlsx yang valid.']);
        }

        if (! $rows) {
            return back()->withErrors(['file' => 'File Excel tidak memiliki baris judul.']);
        }

        $headers = [];
        foreach ($rows[0]['cells'] as $column => $heading) {
            $headers[mb_strtolower(trim($heading))] = $column;
        }
        $allowedHeaders = array_diff(self::HEADERS, ['id']);
        $unknownHeaders = array_diff(array_keys($headers), $allowedHeaders);
        $missingRequiredHeaders = array_diff(['name', 'password'], array_keys($headers));
        $missingExportHeaders = array_key_exists('id', $headers)
            ? array_diff(self::HEADERS, array_keys($headers))
            : [];
        if ($unknownHeaders || $missingRequiredHeaders || $missingExportHeaders) {
            return back()->withErrors(['file' => 'Gunakan kolom template sederhana ('.implode(', ', self::TEMPLATE_HEADERS).') atau file ekspor lengkap untuk memperbarui data.']);
        }

        $departments = Department::query()->get()->keyBy(fn (Department $item) => mb_strtolower($item->name));
        $offices = OfficeSetting::query()->get()->keyBy(fn (OfficeSetting $item) => mb_strtolower($item->name));
        $roles = app(MenuAccessService::class)->managedRoles();
        $roleOptions = [];
        foreach ($roles as $key => $label) {
            $roleOptions[mb_strtolower($key)] = $key;
            $roleOptions[mb_strtolower($label)] = $key;
        }
        $approvalOptions = [];
        foreach (LeaveApprovalWorkflow::LEVELS as $key => $label) {
            $approvalOptions[mb_strtolower($key)] = $key;
            $approvalOptions[mb_strtolower($label)] = $key;
        }
        $year = (int) now()->year;
        $imports = [];
        $errors = [];
        $seen = ['username' => [], 'email' => [], 'nik' => []];
        $seenIds = [];

        foreach (array_slice($rows, 1) as $row) {
            $cells = $row['cells'];
            $values = [];
            foreach (self::HEADERS as $heading) {
                $values[$heading] = isset($headers[$heading]) ? ($cells[$headers[$heading]] ?? '') : '';
            }
            if (count(array_filter($values, fn ($value) => $value !== '')) === 0) {
                continue;
            }

            $line = $row['row'];
            $id = $values['id'] === '' ? null : filter_var($values['id'], FILTER_VALIDATE_INT);
            $user = $id ? User::query()->where('role', '!=', 'superadmin')->find($id) : null;
            if ($values['id'] !== '' && (! $id || ! $user)) {
                $errors[] = "Baris {$line}: ID tidak ditemukan atau bukan akun karyawan yang dapat diimpor.";

                continue;
            }
            if ($id && isset($seenIds[$id])) {
                $errors[] = "Baris {$line}: ID {$id} muncul lebih dari sekali pada file impor.";

                continue;
            }
            if ($id) {
                $seenIds[$id] = true;
            }

            $department = $values['department'] === '' ? null : $departments->get(mb_strtolower($values['department']));
            $office = $values['office'] === '' ? null : $offices->get(mb_strtolower($values['office']));
            $data = [
                'name' => $values['name'],
                'username' => mb_strtolower($values['username']),
                'email' => $values['email'] === '' ? null : $values['email'],
                'nik' => $values['nik'] === '' ? null : $values['nik'],
                'position' => $values['position'] === '' ? null : $values['position'],
                'department_id' => $department?->id,
                'approval_level' => $values['approval_level'] === '' ? null : ($approvalOptions[mb_strtolower($values['approval_level'])] ?? $values['approval_level']),
                'office_id' => $office?->id,
                'role' => $values['role'] === '' ? 'karyawan' : ($roleOptions[mb_strtolower($values['role'])] ?? $values['role']),
                'annual_leave_entitlement' => $values['annual_leave_entitlement'] ?: 12,
            ];
            if (! $user && $data['username'] === '') {
                $data['username'] = $this->generateUsername($values['name'], $seen['username']);
            }
            $active = match (mb_strtolower($values['is_active'])) {
                '1', 'aktif', 'ya', 'true' => true,
                '0', 'nonaktif', 'tidak', 'false' => false,
                '' => true,
                default => null,
            };
            $passwordRules = $user
                ? ['nullable', 'string']
                : ['required', 'string'];
            $validator = Validator::make($data + ['is_active' => $values['is_active'], 'password' => $values['password']], [
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'alpha_dash', 'max:50', Rule::unique('users', 'username')->ignore($user?->id)],
                'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
                'nik' => ['nullable', 'string', 'max:30', Rule::unique('users', 'nik')->ignore($user?->id)],
                'position' => ['nullable', 'string', 'max:120'],
                'department_id' => ['nullable', 'exists:departments,id'],
                'approval_level' => ['nullable', Rule::in(array_keys(LeaveApprovalWorkflow::LEVELS))],
                'office_id' => ['nullable', 'exists:office_settings,id'],
                'role' => ['required', Rule::in(array_keys($roles))],
                'annual_leave_entitlement' => ['required', 'integer', 'between:0,366'],
                'is_active' => [Rule::in(['0', '1', 'aktif', 'nonaktif', 'ya', 'tidak', 'true', 'false'])],
                'password' => $passwordRules,
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors[] = "Baris {$line}: {$message}";
                }

                continue;
            }
            if (($values['department'] !== '' && ! $department) || ($values['office'] !== '' && ! $office)) {
                $errors[] = "Baris {$line}: Nama departemen atau lokasi kerja tidak ditemukan.";

                continue;
            }
            if ($active === null) {
                $errors[] = "Baris {$line}: is_active harus 1/0 atau aktif/nonaktif.";

                continue;
            }
            if (filled($data['approval_level']) && ! $department) {
                $errors[] = "Baris {$line}: Tingkat approval memerlukan departemen.";

                continue;
            }
            if ($this->requiresOfficeAssignment((string) $data['approval_level']) && ! $office) {
                $errors[] = "Baris {$line}: Tingkat approval SPBU/LPG memerlukan lokasi kerja.";

                continue;
            }
            if (! $this->officeMatchesApprovalLevel((string) $data['approval_level'], $office)) {
                $errors[] = "Baris {$line}: Jenis lokasi kerja tidak sesuai dengan tingkat approval.";

                continue;
            }
            foreach (['username', 'email', 'nik'] as $uniqueField) {
                $uniqueValue = $data[$uniqueField] ?? null;
                if ($uniqueValue === null) {
                    continue;
                }
                if (isset($seen[$uniqueField][$uniqueValue])) {
                    $errors[] = "Baris {$line}: {$uniqueField} duplikat pada file impor.";
                }
                $seen[$uniqueField][$uniqueValue] = true;
            }
            if ($user) {
                if ((int) auth()->id() === $user->id && ! $active) {
                    $errors[] = "Baris {$line}: Akun yang sedang digunakan tidak dapat dinonaktifkan.";

                    continue;
                }
                if ($user->is_active && ! $active
                    && $user->role === 'superadmin'
                    && User::query()->where('role', 'superadmin')->where('is_active', true)->count() <= 1) {
                    $errors[] = "Baris {$line}: Superadmin aktif terakhir tidak dapat dinonaktifkan.";

                    continue;
                }
                $assignmentChanged = $user->approval_level !== ($data['approval_level'] ?? null)
                    || (int) $user->department_id !== (int) ($data['department_id'] ?? 0)
                    || (int) $user->office_id !== (int) ($data['office_id'] ?? 0)
                    || (! $active && $user->is_active);
                if ($assignmentChanged && $this->hasOpenAssignedApprovals($user)) {
                    $errors[] = "Baris {$line}: Selesaikan persetujuan yang ditugaskan sebelum mengubah penempatan approver.";

                    continue;
                }
            }

            $data['is_active'] = $active;
            $data['password'] = $values['password'] === '' ? null : $values['password'];
            $data['annual_leave_entitlement'] = (int) $data['annual_leave_entitlement'];
            $imports[] = ['user' => $user, 'data' => $data, 'year' => $year];
        }

        if ($errors) {
            return back()->withErrors(['import' => array_slice($errors, 0, 30)])->with('import_error_count', count($errors));
        }
        if (! $imports) {
            return back()->withErrors(['file' => 'Tidak ada baris data karyawan untuk diimpor.']);
        }

        try {
            DB::transaction(function () use ($imports): void {
                foreach ($imports as $import) {
                    $data = $import['data'];
                    $password = $data['password'];
                    unset($data['password'], $data['annual_leave_entitlement']);
                    if ($password !== null) {
                        $data['password'] = $password;
                    }
                    $user = $import['user'];
                    if ($user) {
                        $user->update($data);
                    } else {
                        $user = User::query()->create($data);
                    }
                    UserAnnualLeaveEntitlement::query()->updateOrCreate(
                        ['user_id' => $user->id, 'year' => $import['year']],
                        ['days' => $import['data']['annual_leave_entitlement']],
                    );
                }
            });
        } catch (Throwable) {
            return back()->withErrors(['file' => 'Impor dibatalkan karena data bertabrakan dengan data terbaru. Tidak ada perubahan yang disimpan.']);
        }

        return back()->with('status', count($imports).' data karyawan berhasil diimpor.');
    }

    private function requiresOfficeAssignment(string $approvalLevel): bool
    {
        return collect(LeaveApprovalWorkflow::OFFICE_SCOPED_LEVEL_PREFIXES)
            ->contains(fn (string $prefix) => str_starts_with($approvalLevel, $prefix));
    }

    private function generateUsername(string $name, array $seenUsernames): string
    {
        $firstName = preg_split('/\s+/u', trim($name))[0] ?? '';
        $base = Str::limit(Str::slug($firstName) ?: 'karyawan', 45, '');

        do {
            $username = $base.random_int(10, 99);
        } while (User::query()->where('username', $username)->exists() || isset($seenUsernames[$username]));

        return $username;
    }

    private function officeMatchesApprovalLevel(string $approvalLevel, ?OfficeSetting $office): bool
    {
        if (! $this->requiresOfficeAssignment($approvalLevel) || ! $office) {
            return true;
        }

        $expectedType = str_starts_with($approvalLevel, 'lpg_') ? 'lpg' : 'spbu';

        return $office->workplace_type === $expectedType;
    }

    private function hasOpenAssignedApprovals(User $user): bool
    {
        return LeaveRequestApproval::query()
            ->where('approver_user_id', $user->id)
            ->whereIn('status', ['Menunggu', 'Belum diproses'])
            ->whereHas('leaveRequest', fn ($query) => $query->where('status', 'Menunggu'))
            ->exists();
    }
}

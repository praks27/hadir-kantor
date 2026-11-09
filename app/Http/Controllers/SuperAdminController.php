<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\EmployeeRole;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\LeaveApprovalWorkflow;
use App\Models\MenuAccessSetting;
use App\Models\OfficeSetting;
use App\Models\User;
use App\Models\UserAnnualLeaveEntitlement;
use App\Services\MenuAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    private const RESERVED_ROLE_KEYS = ['karyawan', 'admin', 'superadmin', 'super_admin'];
    private const WORKFLOW_GROUPS = [
        'kantor' => ['staff', 'supervisor', 'hod'],
        'spbu' => ['spbu_operator', 'spbu_supervisor', 'spbu_manager', 'hod'],
        'lpg' => ['lpg_driver', 'lpg_operator', 'lpg_supervisor', 'lpg_manager', 'hod'],
    ];

    public function departments(): View
    {
        return view('superadmin.departments', [
            'departments' => Department::query()
                ->withCount(['users', 'leaveApprovalWorkflows'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function storeDepartment(Request $request): RedirectResponse
    {
        $request->merge(['name' => trim((string) $request->input('name', ''))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:departments,name'],
        ]);
        Department::query()->create($data);

        return to_route('superadmin.departments')->with('status', 'Departemen berhasil ditambahkan. Departemen ini sekarang tersedia untuk akun pengguna dan pengaturan alur persetujuan.');
    }

    public function updateDepartment(Request $request, Department $department): RedirectResponse
    {
        $request->merge(['name' => trim((string) $request->input('name', ''))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('departments', 'name')->ignore($department)],
        ]);
        $department->update($data);

        return to_route('superadmin.departments')->with('status', 'Nama departemen berhasil diperbarui.');
    }

    public function destroyDepartment(Department $department): RedirectResponse
    {
        if ($department->users()->exists()) {
            return back()->withErrors(['department' => 'Departemen masih dipakai akun pengguna. Pindahkan akun tersebut ke departemen lain sebelum menghapusnya.']);
        }

        $department->leaveApprovalWorkflows()->delete();
        $department->delete();

        return to_route('superadmin.departments')->with('status', 'Departemen berhasil dihapus.');
    }

    public function office(Request $request): View
    {
        $editingOffice = $request->filled('edit')
            ? OfficeSetting::findOrFail($request->integer('edit'))
            : null;
        $offices = OfficeSetting::query()->withCount(['attendances', 'leaveRequests'])->orderBy('name')->get();
        $setting = $editingOffice ?? OfficeSetting::defaults();
        if (! $editingOffice && $offices->isNotEmpty()) {
            $setting->name = '';
        }

        return view('superadmin.office', [
            'offices' => $offices,
            'setting' => $setting,
            'editingOffice' => $editingOffice,
        ]);
    }

    public function storeOffice(Request $request): RedirectResponse
    {
        OfficeSetting::query()->create($this->validatedOfficeData($request));

        return to_route('superadmin.office')->with('status', 'Lokasi kantor berhasil ditambahkan.');
    }

    public function updateOffice(Request $request, OfficeSetting $officeSetting): RedirectResponse
    {
        $officeSetting->update($this->validatedOfficeData($request));

        return to_route('superadmin.office')->with('status', 'Pengaturan lokasi kantor berhasil diperbarui.');
    }

    public function destroyOffice(OfficeSetting $officeSetting): RedirectResponse
    {
        if (OfficeSetting::query()->count() <= 1) {
            return back()->withErrors(['office' => 'Setidaknya satu lokasi kantor harus tetap tersedia.']);
        }

        if (Attendance::query()->where('office_id', $officeSetting->id)->exists()
            || LeaveRequest::query()->where('office_id', $officeSetting->id)->exists()) {
            return back()->withErrors(['office' => 'Lokasi ini sudah digunakan dalam catatan absensi atau pengajuan izin, sehingga tidak dapat dihapus.']);
        }

        $officeSetting->delete();

        return to_route('superadmin.office')->with('status', 'Lokasi kantor berhasil dihapus.');
    }

    private function validatedOfficeData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'workplace_type' => ['required', Rule::in(['kantor', 'spbu', 'lpg'])],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required', 'integer', 'between:10,10000'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'grace_minutes' => ['required', 'integer', 'between:0,180'],
            'work_days' => ['required', 'array', 'min:1'],
            'work_days.*' => ['required', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])],
        ]);
        $data['work_days'] = array_values(array_unique($data['work_days']));

        return $data;
    }

    public function users(Request $request): View
    {
        $leaveEntitlementYear = (int) now()->year;
        $users = User::query()
            ->with('department')
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = '%'.$request->string('q')->trim()->toString().'%';
                $query->where(fn ($query) => $query->where('name', 'like', $search)
                    ->orWhere('username', 'like', $search)
                    ->orWhere('nik', 'like', $search));
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();
        $leaveEntitlements = UserAnnualLeaveEntitlement::query()
            ->where('year', $leaveEntitlementYear)
            ->whereIn('user_id', $users->getCollection()->modelKeys())
            ->pluck('days', 'user_id');

        return view('superadmin.users', [
            'users' => $users,
            'leaveEntitlements' => $leaveEntitlements,
            'leaveEntitlementYear' => $leaveEntitlementYear,
            'departments' => Department::query()->orderBy('name')->get(),
            'offices' => OfficeSetting::query()->orderBy('name')->get(),
            'roles' => app(MenuAccessService::class)->managedRoles(),
            'employeeRoles' => EmployeeRole::query()->orderBy('name')->get(),
            'approvalLevels' => LeaveApprovalWorkflow::LEVELS,
        ]);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $name = trim((string) $request->input('name', ''));
        $username = mb_strtolower(trim((string) $request->input('username', '')));
        if (! $this->usernameMatchesName($username, $name)
            || User::query()->where('username', $username)->exists()) {
            $username = $this->generateUsername($name);
        }
        $request->merge(['name' => $name, 'username' => $username]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'alpha_dash', 'max:50', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'annual_leave_entitlement' => ['nullable', 'integer', 'between:0,366'],
            'nik' => ['nullable', 'string', 'max:30', 'unique:users,nik'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'approval_level' => ['nullable', Rule::in(array_keys(LeaveApprovalWorkflow::LEVELS))],
            'office_id' => ['nullable', 'exists:office_settings,id'],
            'position' => ['nullable', 'string', 'max:120'],
            'role' => ['required', Rule::in(array_keys(app(MenuAccessService::class)->managedRoles()))],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $annualLeaveDays = (int) ($data['annual_leave_entitlement'] ?? 12);
        unset($data['annual_leave_entitlement']);
        if (filled($data['approval_level'] ?? null) && empty($data['department_id'])) {
            return back()->withErrors(['department_id' => 'Pengguna dengan tingkat approval harus ditempatkan pada departemen.'])->withInput();
        }
        if ($this->requiresOfficeAssignment((string) ($data['approval_level'] ?? '')) && empty($data['office_id'])) {
            return back()->withErrors(['office_id' => 'Approver SPBU atau agen LPG harus ditempatkan pada lokasi kerja yang sesuai.'])->withInput();
        }
        if (! $this->officeMatchesApprovalLevel((string) ($data['approval_level'] ?? ''), $data['office_id'] ?? null)) {
            return back()->withErrors(['office_id' => 'Jenis lokasi kerja harus sesuai dengan jabatan approver.'])->withInput();
        }
        $user = User::query()->create($data);
        UserAnnualLeaveEntitlement::query()->updateOrCreate(
            ['user_id' => $user->id, 'year' => (int) now()->year],
            ['days' => $annualLeaveDays],
        );

        return back()->with('status', 'Akun pengguna berhasil dibuat.');
    }

    public function storeEmployeeRole(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:employee_roles,name'],
        ]);
        $key = Str::slug($data['name'], '_');
        if ($key === '' || in_array($key, self::RESERVED_ROLE_KEYS, true)
            || EmployeeRole::query()->where('key', $key)->exists()) {
            return back()->withErrors(['name' => 'Nama role ini sudah digunakan atau tidak dapat dijadikan role karyawan.'])->withInput();
        }

        EmployeeRole::query()->create(['key' => $key, 'name' => trim($data['name'])]);

        return to_route('superadmin.users')->with('status', 'Role karyawan berhasil ditambahkan.');
    }

    public function updateUser(Request $request, User $user, MenuAccessService $menuAccess): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', 'string', 'min:8'],
            'annual_leave_entitlement' => ['required', 'integer', 'between:0,366'],
            'nik' => ['nullable', 'string', 'max:30', Rule::unique('users', 'nik')->ignore($user)],
            'department_id' => ['nullable', 'exists:departments,id'],
            'approval_level' => ['nullable', Rule::in(array_keys(LeaveApprovalWorkflow::LEVELS))],
            'office_id' => ['nullable', 'exists:office_settings,id'],
            'position' => ['nullable', 'string', 'max:120'],
            'role' => ['required', Rule::in(array_merge(array_keys($menuAccess->managedRoles()), ['superadmin']))],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $annualLeaveDays = (int) $data['annual_leave_entitlement'];
        unset($data['annual_leave_entitlement']);

        if ($data['role'] === 'superadmin' && ! $user->isSuperAdmin()) {
            return back()->withErrors(['role' => 'Role superadmin hanya dapat dimiliki akun superadmin yang sudah ada.'])->withInput();
        }

        $assignmentChanged = $user->approval_level !== ($data['approval_level'] ?? null)
            || (int) $user->department_id !== (int) ($data['department_id'] ?? 0)
            || (int) $user->office_id !== (int) ($data['office_id'] ?? 0)
            || (! $request->boolean('is_active') && $user->is_active);
        if ($assignmentChanged && $this->hasOpenAssignedApprovals($user)) {
            return back()->withErrors(['approval_level' => 'Selesaikan atau tunggu seluruh tahap izin yang sudah ditugaskan sebelum mengubah penempatan approver.'])->withInput();
        }

        $willLoseSuperAdminAccess = ! $request->boolean('is_active') || $data['role'] !== 'superadmin';
        if ((int) Auth::id() === $user->id && $willLoseSuperAdminAccess) {
            return back()->withErrors(['role' => 'Anda tidak dapat menonaktifkan akun atau mengubah role sendiri.'])->withInput();
        }
        if ($user->isSuperAdmin() && $user->is_active && $willLoseSuperAdminAccess
            && User::query()->where('role', 'superadmin')->where('is_active', true)->count() <= 1) {
            return back()->withErrors(['role' => 'Buat superadmin aktif lain sebelum menonaktifkan atau mengubah role akun ini.'])->withInput();
        }

        $data['is_active'] = $request->boolean('is_active');
        if (filled($data['approval_level'] ?? null) && empty($data['department_id'])) {
            return back()->withErrors(['department_id' => 'Pengguna dengan tingkat approval harus ditempatkan pada departemen.'])->withInput();
        }
        if ($this->requiresOfficeAssignment((string) ($data['approval_level'] ?? '')) && empty($data['office_id'])) {
            return back()->withErrors(['office_id' => 'Approver SPBU atau agen LPG harus ditempatkan pada lokasi kerja yang sesuai.'])->withInput();
        }
        if (! $this->officeMatchesApprovalLevel((string) ($data['approval_level'] ?? ''), $data['office_id'] ?? null)) {
            return back()->withErrors(['office_id' => 'Jenis lokasi kerja harus sesuai dengan jabatan approver.'])->withInput();
        }
        if (blank($data['password'])) {
            unset($data['password']);
        }
        $user->update($data);
        UserAnnualLeaveEntitlement::query()->updateOrCreate(
            ['user_id' => $user->id, 'year' => (int) now()->year],
            ['days' => $annualLeaveDays],
        );
        $menuAccess->forget($user->id);

        return back()->with('status', 'Data pengguna berhasil diperbarui.');
    }

    public function destroyUser(User $user): RedirectResponse
    {
        if ((int) Auth::id() === $user->id) {
            return back()->withErrors(['user' => 'Anda tidak dapat menghapus akun yang sedang digunakan.']);
        }
        if ($user->isSuperAdmin() && User::query()->where('role', 'superadmin')->where('is_active', true)->count() <= 1) {
            return back()->withErrors(['user' => 'Akun superadmin aktif terakhir tidak dapat dihapus.']);
        }
        if ($this->hasOpenAssignedApprovals($user)) {
            return back()->withErrors(['user' => 'Akun ini masih memiliki tahap persetujuan izin yang ditugaskan dan belum selesai.']);
        }

        MenuAccessSetting::query()->where('scope_type', 'user')->where('scope_key', (string) $user->id)->delete();
        $user->delete();

        return back()->with('status', 'Akun pengguna berhasil dihapus.');
    }

    public function menuAccess(Request $request, MenuAccessService $menuAccess): View
    {
        $selectedRole = $request->query('role', 'karyawan');
        $roles = $menuAccess->managedRoles();
        if (! array_key_exists($selectedRole, $roles)) {
            $selectedRole = 'karyawan';
        }
        $selectedUser = $request->filled('user')
            ? User::query()->where('role', '!=', 'superadmin')->find($request->integer('user'))
            : null;
        $rolePermissions = $menuAccess->permissionsForRole($selectedRole);
        $userOverrides = $selectedUser
            ? (MenuAccessSetting::query()->where('scope_type', 'user')->where('scope_key', (string) $selectedUser->id)->first()?->permissions ?? [])
            : [];
        if (array_key_exists('reports', $userOverrides)) {
            foreach (['report_overview', 'report_leaves', 'report_attendance'] as $reportMenu) {
                if (! array_key_exists($reportMenu, $userOverrides)) {
                    $userOverrides[$reportMenu] = $userOverrides['reports'];
                }
            }
        }

        return view('superadmin.menu-access', [
            'menus' => MenuAccessService::MENUS,
            'menuDescriptions' => MenuAccessService::MENU_DESCRIPTIONS,
            'roles' => $roles,
            'selectedRole' => $selectedRole,
            'rolePermissions' => $rolePermissions,
            'users' => User::query()->where('role', '!=', 'superadmin')->orderBy('name')->get(['id', 'name', 'username', 'role']),
            'selectedUser' => $selectedUser,
            'userOverrides' => $userOverrides,
        ]);
    }

    public function updateMenuAccess(Request $request, MenuAccessService $menuAccess): RedirectResponse
    {
        $scope = $request->validate([
            'scope' => ['required', Rule::in(['role', 'user'])],
        ])['scope'];

        if ($scope === 'role') {
            $data = $request->validate([
                'role' => ['required', Rule::in(array_keys($menuAccess->managedRoles()))],
                'permissions' => ['nullable', 'array'],
                'permissions.*' => ['boolean'],
            ]);
            $permissions = [];
            foreach (MenuAccessService::MENUS as $key => $_label) {
                $permissions[$key] = $request->boolean('permissions.'.$key);
            }

            MenuAccessSetting::query()->updateOrCreate(
                ['scope_type' => 'role', 'scope_key' => $data['role']],
                ['permissions' => $permissions],
            );
            $menuAccess->forget();

            return to_route('superadmin.menu-access', ['role' => $data['role']])
                ->with('status', 'Hak akses umum untuk role berhasil diperbarui.');
        }

        if ($request->boolean('reset_to_role')) {
            $data = $request->validate([
                'user_id' => ['required', 'integer', 'exists:users,id'],
            ]);
            $user = User::query()->where('role', '!=', 'superadmin')->findOrFail($data['user_id']);
            MenuAccessSetting::query()->where('scope_type', 'user')->where('scope_key', (string) $user->id)->delete();
            $menuAccess->forget($user->id);

            return to_route('superadmin.menu-access', ['role' => $user->role, 'user' => $user->id])
                ->with('status', 'Hak akses khusus dihapus. Akun sekarang mengikuti seluruh pengaturan role.');
        }

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['nullable', Rule::in(['0', '1'])],
        ]);
        $user = User::query()->where('role', '!=', 'superadmin')->findOrFail($data['user_id']);
        $permissions = [];
        foreach (MenuAccessService::MENUS as $key => $_label) {
            $value = $data['permissions'][$key] ?? null;
            $permissions[$key] = $value === null || $value === '' ? null : $value === '1' || $value === 1;
        }

        if (collect($permissions)->every(fn ($value) => $value === null)) {
            MenuAccessSetting::query()->where('scope_type', 'user')->where('scope_key', (string) $user->id)->delete();
        } else {
            MenuAccessSetting::query()->updateOrCreate(
                ['scope_type' => 'user', 'scope_key' => (string) $user->id],
                ['permissions' => $permissions],
            );
        }
        $menuAccess->forget($user->id);

        return to_route('superadmin.menu-access', ['role' => $user->role, 'user' => $user->id])
            ->with('status', 'Hak akses khusus karyawan berhasil diperbarui.');
    }

    public function approvalWorkflows(): View
    {
        $workflows = LeaveApprovalWorkflow::query()->get();

        return view('superadmin.workflows', [
            'workflows' => $workflows->keyBy(fn (LeaveApprovalWorkflow $workflow) => $workflow->workplace_type.'.'.($workflow->department_id ?? 'general').'.'.$workflow->requester_level),
            'departments' => Department::query()->orderBy('name')->get(),
            'groups' => self::WORKFLOW_GROUPS,
            'levels' => LeaveApprovalWorkflow::LEVELS,
            'approverLevels' => array_intersect_key(LeaveApprovalWorkflow::LEVELS, array_flip(LeaveApprovalWorkflow::APPROVER_LEVELS)),
        ]);
    }

    public function updateApprovalWorkflows(Request $request): RedirectResponse
    {
        $payload = $request->input('workflows', []);
        if (! is_array($payload)) {
            $payload = [];
        }
        foreach ($payload as &$workflowData) {
            if (isset($workflowData['approval_steps']) && is_array($workflowData['approval_steps'])) {
                $workflowData['approval_steps'] = array_values(array_filter($workflowData['approval_steps'], fn ($step) => filled($step)));
            }
        }
        unset($workflowData);
        $departmentPayload = $request->input('department_workflows', []);
        if (! is_array($departmentPayload)) {
            $departmentPayload = [];
        }
        foreach ($departmentPayload as &$departmentData) {
            if (! is_array($departmentData)) {
                $departmentData = [];
                continue;
            }
            foreach ($departmentData as &$workflowData) {
                if (isset($workflowData['approval_steps']) && is_array($workflowData['approval_steps'])) {
                    $workflowData['approval_steps'] = array_values(array_filter($workflowData['approval_steps'], fn ($step) => filled($step)));
                }
            }
            unset($workflowData);
        }
        unset($departmentData);

        $validator = validator([
            'workflows' => $payload,
            'department_workflows' => $departmentPayload,
        ], [
            'workflows.*.approval_steps' => ['required', 'array', 'min:1', 'max:4'],
            'workflows.*.approval_steps.*' => ['required', Rule::in(LeaveApprovalWorkflow::APPROVER_LEVELS)],
            'department_workflows' => ['nullable', 'array'],
            'department_workflows.*' => ['array'],
            'department_workflows.*.*.approval_steps' => ['nullable', 'array', 'max:4'],
            'department_workflows.*.*.approval_steps.*' => ['required', Rule::in(LeaveApprovalWorkflow::APPROVER_LEVELS)],
        ]);

        foreach (self::WORKFLOW_GROUPS as $workplaceType => $requesterLevels) {
            foreach ($requesterLevels as $requesterLevel) {
                $workflowKey = $this->workflowFormKey($workplaceType, $requesterLevel);
                $steps = $payload[$workflowKey]['approval_steps'] ?? [];
                if (count($steps) !== count(array_unique($steps))) {
                    $validator->errors()->add("workflows.{$workflowKey}.approval_steps", 'Tingkat approver tidak boleh berulang dalam satu alur.');
                }
                if ($requesterLevel !== 'hod' && in_array('director', $steps, true)) {
                    $validator->errors()->add("workflows.{$workflowKey}.approval_steps", 'Direksi hanya dapat menjadi approver untuk pengajuan HOD.');
                }
            }
        }

        $officeRequesterLevels = self::WORKFLOW_GROUPS['kantor'];
        $departmentIds = Department::query()->pluck('id')->map(fn ($id) => (string) $id)->all();
        foreach ($departmentPayload as $departmentId => $departmentData) {
            if (! in_array((string) $departmentId, $departmentIds, true)) {
                $validator->errors()->add("department_workflows.{$departmentId}", 'Departemen tidak valid.');
                continue;
            }
            foreach ($departmentData as $requesterLevel => $workflowData) {
                if (! is_array($workflowData)) {
                    continue;
                }
                if (! in_array($requesterLevel, $officeRequesterLevels, true)) {
                    $validator->errors()->add("department_workflows.{$departmentId}.{$requesterLevel}", 'Tingkat jabatan pemohon tidak valid untuk alur kantor.');
                    continue;
                }
                $steps = $workflowData['approval_steps'] ?? [];
                if (count($steps) !== count(array_unique($steps))) {
                    $validator->errors()->add("department_workflows.{$departmentId}.{$requesterLevel}.approval_steps", 'Tingkat approver tidak boleh berulang dalam satu alur.');
                }
                if ($requesterLevel !== 'hod' && in_array('director', $steps, true)) {
                    $validator->errors()->add("department_workflows.{$departmentId}.{$requesterLevel}.approval_steps", 'Direksi hanya dapat menjadi approver untuk pengajuan HOD.');
                }
            }
        }

        $expected = collect(self::WORKFLOW_GROUPS)->flatMap(fn (array $requesterLevels, string $workplaceType) => array_map(
            fn (string $requesterLevel) => $this->workflowFormKey($workplaceType, $requesterLevel),
            $requesterLevels,
        ))->all();
        foreach ($expected as $key) {
            if (! isset($payload[$key])) {
                $validator->errors()->add($key, 'Setiap kombinasi lokasi dan jabatan harus memiliki alur.');
            }
        }
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($payload, $departmentPayload, $officeRequesterLevels): void {
            LeaveApprovalWorkflow::query()->where('requester_level', 'director')->delete();

            foreach (self::WORKFLOW_GROUPS as $workplaceType => $requesterLevels) {
                foreach ($requesterLevels as $requesterLevel) {
                    $key = $this->workflowFormKey($workplaceType, $requesterLevel);
                    LeaveApprovalWorkflow::query()->updateOrCreate(
                        ['workplace_type' => $workplaceType, 'department_id' => null, 'requester_level' => $requesterLevel],
                        ['approval_steps' => $payload[$key]['approval_steps']],
                    );
                }
            }

            foreach (Department::query()->pluck('id') as $departmentId) {
                foreach ($officeRequesterLevels as $requesterLevel) {
                    $steps = $departmentPayload[$departmentId][$requesterLevel]['approval_steps'] ?? [];
                    $workflowQuery = LeaveApprovalWorkflow::query()
                        ->where('workplace_type', 'kantor')
                        ->where('department_id', $departmentId)
                        ->where('requester_level', $requesterLevel);
                    if ($steps === []) {
                        $workflowQuery->delete();
                    } else {
                        LeaveApprovalWorkflow::query()->updateOrCreate(
                            ['workplace_type' => 'kantor', 'department_id' => $departmentId, 'requester_level' => $requesterLevel],
                            ['approval_steps' => $steps],
                        );
                    }
                }
            }
        });

        return to_route('superadmin.workflows')->with('status', 'Alur persetujuan berhasil diperbarui.');
    }

    private function workflowFormKey(string $workplaceType, string $requesterLevel): string
    {
        return $workplaceType.'_'.$requesterLevel;
    }

    private function usernameBase(string $name): string
    {
        $firstName = preg_split('/\s+/u', trim($name))[0] ?? '';

        return Str::limit(Str::slug($firstName) ?: 'karyawan', 45, '');
    }

    private function usernameMatchesName(string $username, string $name): bool
    {
        $base = $this->usernameBase($name);

        return preg_match('/^'.preg_quote($base, '/').'\d{2}$/', $username) === 1;
    }

    private function generateUsername(string $name): string
    {
        $base = Str::limit($this->usernameBase($name), 48, '');

        do {
            $username = $base.random_int(10, 99);
        } while (User::query()->where('username', $username)->exists());

        return $username;
    }

    private function requiresOfficeAssignment(string $approvalLevel): bool
    {
        return collect(LeaveApprovalWorkflow::OFFICE_SCOPED_LEVEL_PREFIXES)
            ->contains(fn (string $prefix) => str_starts_with($approvalLevel, $prefix));
    }

    private function officeMatchesApprovalLevel(string $approvalLevel, mixed $officeId): bool
    {
        if (! $this->requiresOfficeAssignment($approvalLevel) || blank($officeId)) {
            return true;
        }

        $expectedType = str_starts_with($approvalLevel, 'lpg_') ? 'lpg' : 'spbu';

        return OfficeSetting::query()->whereKey($officeId)->where('workplace_type', $expectedType)->exists();
    }

    private function hasOpenAssignedApprovals(User $user): bool
    {
        return $user->assignedLeaveApprovals()
            ->whereIn('status', ['Menunggu', 'Belum diproses'])
            ->whereHas('leaveRequest', fn ($query) => $query->where('status', 'Menunggu'))
            ->exists();
    }
}

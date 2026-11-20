<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\LeaveApprovalWorkflow;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestApproval;
use App\Models\OfficeSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use RuntimeException;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $it = Department::query()->firstOrCreate(['name' => 'Teknologi Informasi']);
        $operations = Department::query()->firstOrCreate(['name' => 'Operasional SPBU']);

        $office = OfficeSetting::query()->updateOrCreate(
            ['name' => 'Kantor Pusat Demo'],
            [
                'workplace_type' => 'kantor',
                'latitude' => config('attendance.office.latitude', -6.2),
                'longitude' => config('attendance.office.longitude', 106.8166667),
                'radius_meters' => config('attendance.office.radius_meters', 150),
                'start_time' => config('attendance.office.start_time', '08:00'),
                'end_time' => config('attendance.office.end_time', '17:00'),
                'grace_minutes' => config('attendance.office.grace_minutes', 15),
                'work_days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'],
            ],
        );
        $spbu = OfficeSetting::query()->updateOrCreate(
            ['name' => 'SPBU Demo'],
            [
                'workplace_type' => 'spbu',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'radius_meters' => 150,
                'start_time' => '07:00',
                'end_time' => '19:00',
                'grace_minutes' => 10,
                'work_days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
            ],
        );
        $lpg = OfficeSetting::query()->updateOrCreate(
            ['name' => 'Agen LPG Demo'],
            [
                'workplace_type' => 'lpg',
                'latitude' => -6.2146,
                'longitude' => 106.8294,
                'radius_meters' => 150,
                'start_time' => '07:00',
                'end_time' => '19:00',
                'grace_minutes' => 10,
                'work_days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
            ],
        );

        $admin = $this->user('admin', 'Admin HadirKantor', 'admin@hadirkantor.test', 'ADM-0001', $it, null, null, 'superadmin');
        $staff = $this->user('budi', 'Budi Santoso', 'karyawan@hadirkantor.test', 'EMP-0001', $it, 'staff', $office);
        $supervisor = $this->user('supervisor.it', 'Sari Wibowo', 'supervisor.it@hadirkantor.test', 'SPV-IT-01', $it, 'supervisor', null);
        $hod = $this->user('hod.it', 'Rina Pratama', 'hod.it@hadirkantor.test', 'HOD-IT-01', $it, 'hod', null);
        $director = $this->user('direksi.it', 'Agus Wijaya', 'direksi.it@hadirkantor.test', 'DIR-IT-01', $it, 'director', null);

        $operator = $this->user('operator.spbu', 'Dedi Saputra', 'operator.spbu@hadirkantor.test', 'EMP-SPBU-01', $operations, 'spbu_operator', $spbu);
        $spbuSupervisor = $this->user('pengawas.spbu', 'Maya Lestari', 'pengawas.spbu@hadirkantor.test', 'SPV-SPBU-01', $operations, 'spbu_supervisor', $spbu);
        $spbuManager = $this->user('pengelola.spbu', 'Hendra Kurniawan', 'pengelola.spbu@hadirkantor.test', 'MGR-SPBU-01', $operations, 'spbu_manager', $spbu);
        $operationsHod = $this->user('hod.ops', 'Nina Permata', 'hod.ops@hadirkantor.test', 'HOD-OPS-01', $operations, 'hod', null);
        $operationsDirector = $this->user('direksi.ops', 'Bambang Setiawan', 'direksi.ops@hadirkantor.test', 'DIR-OPS-01', $operations, 'director', null);
        $lpgOperator = $this->user('sopir.lpg', 'Andi Pratama', 'sopir.lpg@hadirkantor.test', 'EMP-LPG-01', $operations, 'lpg_driver', $lpg);
        $this->user('pengawas.lpg', 'Dewi Anggraini', 'pengawas.lpg@hadirkantor.test', 'SPV-LPG-01', $operations, 'lpg_supervisor', $lpg);
        $this->user('pengelola.lpg', 'Fajar Nugraha', 'pengelola.lpg@hadirkantor.test', 'MGR-LPG-01', $operations, 'lpg_manager', $lpg);

        $this->seedWorkflows();
        $this->seedAttendance($staff, $office);
        $this->seedLeaveRequest($staff, $office, 'Izin', 'Menunggu', 'DATA UJI: Izin staff menunggu persetujuan Supervisor.');
        $this->seedLeaveRequest($staff, $office, 'Cuti', 'Disetujui', 'DATA UJI: Cuti staff yang sudah disetujui.', Carbon::today()->subDays(20), Carbon::today()->subDays(18));
        $this->seedLeaveRequest($staff, $office, 'Izin sakit', 'Ditolak', 'DATA UJI: Izin sakit staff yang ditolak.', Carbon::today()->subDays(30), Carbon::today()->subDays(29));
        $this->seedLeaveRequest($operator, $spbu, 'Izin', 'Menunggu', 'DATA UJI: Izin operator SPBU menunggu Pengawas SPBU.');
        $this->seedLeaveRequest($lpgOperator, $lpg, 'Izin', 'Menunggu', 'DATA UJI: Izin operator agen LPG menunggu Pengawas Agen LPG.');

        $this->command?->info('Data demo siap. Kata sandi semua akun demo: password123');
        $this->command?->line('Login: admin, budi, supervisor.it, hod.it, direksi.it, operator.spbu, pengawas.spbu, pengelola.spbu, sopir.lpg, pengawas.lpg, pengelola.lpg, hod.ops, direksi.ops.');
    }

    private function user(
        string $username,
        string $name,
        string $email,
        string $nik,
        Department $department,
        ?string $approvalLevel,
        ?OfficeSetting $office,
        string $role = 'karyawan',
    ): User {
        return User::query()->updateOrCreate(
            ['username' => $username],
            [
                'name' => $name,
                'email' => $email,
                'password' => 'password123',
                'nik' => $nik,
                'department_id' => $department->id,
                'position' => $approvalLevel ? LeaveApprovalWorkflow::LEVELS[$approvalLevel] : 'Administrator',
                'approval_level' => $approvalLevel,
                'office_id' => $office?->id,
                'role' => $role,
                'is_active' => true,
            ],
        );
    }

    private function seedWorkflows(): void
    {
        $workflows = [
            ['kantor', 'staff', ['supervisor', 'hod']],
            ['kantor', 'supervisor', ['hod']],
            ['kantor', 'hod', ['director']],
            ['spbu', 'spbu_operator', ['spbu_supervisor', 'spbu_manager']],
            ['spbu', 'spbu_supervisor', ['spbu_manager', 'hod']],
            ['spbu', 'spbu_manager', ['hod']],
            ['spbu', 'hod', ['director']],
            ['lpg', 'lpg_operator', ['lpg_supervisor', 'lpg_manager']],
            ['lpg', 'lpg_driver', ['lpg_supervisor', 'lpg_manager']],
            ['lpg', 'lpg_supervisor', ['lpg_manager', 'hod']],
            ['lpg', 'lpg_manager', ['hod']],
            ['lpg', 'hod', ['director']],
        ];

        foreach ($workflows as [$workplaceType, $requesterLevel, $steps]) {
            LeaveApprovalWorkflow::query()->firstOrCreate(
                ['workplace_type' => $workplaceType, 'department_id' => null, 'requester_level' => $requesterLevel],
                ['approval_steps' => $steps],
            );
        }
    }

    private function seedAttendance(User $staff, OfficeSetting $office): void
    {
        for ($daysAgo = 1; $daysAgo <= 7; $daysAgo++) {
            $date = Carbon::today()->subDays($daysAgo);
            if ($date->isWeekend()) {
                continue;
            }

            Attendance::query()->updateOrCreate(
                ['user_id' => $staff->id, 'attendance_date' => $date->toDateString()],
                [
                    'office_id' => $office->id,
                    'check_in' => $date->copy()->setTime(8, 0),
                    'check_out' => $date->copy()->setTime(17, 0),
                    'latitude' => $office->latitude,
                    'longitude' => $office->longitude,
                    'distance_meters' => 12,
                    'check_out_latitude' => $office->latitude,
                    'check_out_longitude' => $office->longitude,
                    'check_out_distance_meters' => 12,
                    'status' => 'Tepat Waktu',
                ],
            );
        }
    }

    private function seedLeaveRequest(
        User $requester,
        OfficeSetting $office,
        string $type,
        string $status,
        string $reason,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
    ): void {
        $startDate ??= Carbon::tomorrow();
        $endDate ??= $startDate->copy()->addDay();
        $workflowQuery = LeaveApprovalWorkflow::query()
            ->where('workplace_type', $office->workplace_type)
            ->where('requester_level', $requester->approval_level)
            ->when($office->workplace_type === 'kantor', fn ($query) => $query->where('department_id', $requester->department_id));
        $workflow = $office->workplace_type === 'kantor'
            ? $workflowQuery->first() ?? LeaveApprovalWorkflow::query()
                ->where('workplace_type', $office->workplace_type)
                ->whereNull('department_id')
                ->where('requester_level', $requester->approval_level)
                ->first()
            : $workflowQuery->whereNull('department_id')->first();

        if (! $workflow || empty($workflow->approval_steps)) {
            throw new RuntimeException("Workflow demo tidak ditemukan untuk {$office->workplace_type}/{$requester->approval_level}.");
        }

        $approvers = [];
        foreach ($workflow->approval_steps as $level) {
            if (! in_array($level, LeaveApprovalWorkflow::APPROVER_LEVELS, true)) {
                throw new RuntimeException("Workflow demo memakai tingkat approval yang tidak didukung: {$level}.");
            }

            $query = User::query()->where('is_active', true)->where('approval_level', $level)
                ->where('department_id', $requester->department_id)->whereKeyNot($requester->id);
            if (str_starts_with($level, 'spbu_') || str_starts_with($level, 'lpg_')) {
                $query->where('office_id', $office->id);
            }
            $approver = $query->orderBy('id')->first();
            if (! $approver) {
                throw new RuntimeException("Approver demo {$level} belum tersedia untuk departemen {$requester->department_id}.");
            }
            $approvers[] = $approver;
        }

        $request = LeaveRequest::query()->updateOrCreate(
            ['user_id' => $requester->id, 'reason' => $reason],
            [
                'office_id' => $office->id,
                'leave_type' => $type,
                'special_type' => null,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'status' => $status,
                'current_approval_step' => $status === 'Disetujui' ? count($approvers) : 1,
                'reviewed_by' => $status === 'Ditolak' ? $approvers[0]->id : null,
                'review_note' => $status === 'Ditolak' ? 'Data demo untuk pengujian.' : null,
                'reviewed_at' => $status === 'Ditolak' ? now() : null,
            ],
        );

        $request->approvals()->delete();
        foreach ($approvers as $index => $approver) {
            $stageStatus = match ($status) {
                'Disetujui' => 'Disetujui',
                'Ditolak' => $index === 0 ? 'Ditolak' : 'Belum diproses',
                default => $index === 0 ? 'Menunggu' : 'Belum diproses',
            };
            LeaveRequestApproval::query()->create([
                'leave_request_id' => $request->id,
                'sequence' => $index + 1,
                'approver_level' => $workflow->approval_steps[$index],
                'approver_user_id' => $approver->id,
                'status' => $stageStatus,
                'reviewed_by' => in_array($stageStatus, ['Disetujui', 'Ditolak'], true) ? $approver->id : null,
                'review_note' => $stageStatus === 'Ditolak' ? 'Data demo untuk pengujian.' : null,
                'reviewed_at' => in_array($stageStatus, ['Disetujui', 'Ditolak'], true) ? now() : null,
            ]);
        }
    }
}

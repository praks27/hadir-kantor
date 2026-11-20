<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveApprovalWorkflow extends Model
{
    protected $fillable = ['workplace_type', 'department_id', 'requester_level', 'approval_steps'];

    protected function casts(): array
    {
        return ['approval_steps' => 'array'];
    }

    public const LEVELS = [
        'staff' => 'Staff / Karyawan',
        'supervisor' => 'Supervisor',
        'hod' => 'Head of Department (HOD)',
        'director' => 'Direksi',
        'spbu_operator' => 'Operator SPBU',
        'spbu_supervisor' => 'Karu / Pengawas SPBU',
        'spbu_manager' => 'Pengelola SPBU',
        'lpg_operator' => 'Operator Agen LPG',
        'lpg_driver' => 'Sopir LPG',
        'lpg_supervisor' => 'Pengawas Agen LPG',
        'lpg_manager' => 'Pengelola Agen LPG',
    ];

    public const APPROVER_LEVELS = [
        'supervisor',
        'hod',
        'director',
        'spbu_supervisor',
        'spbu_manager',
        'lpg_supervisor',
        'lpg_manager',
    ];

    public const OFFICE_SCOPED_LEVEL_PREFIXES = ['spbu_', 'lpg_'];
}

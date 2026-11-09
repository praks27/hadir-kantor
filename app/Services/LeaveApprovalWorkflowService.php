<?php

namespace App\Services;

use App\Models\LeaveApprovalWorkflow;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class LeaveApprovalWorkflowService
{
    private array $actionableCounts = [];

    public function actionableCount(User $approver): int
    {
        if (! array_key_exists($approver->id, $this->actionableCounts)) {
            $this->actionableCounts[$approver->id] = $this->actionableRequests($approver)->count();
        }

        return $this->actionableCounts[$approver->id];
    }

    public function eligibleApprovers(string $level, LeaveRequest $request): Builder
    {
        $query = User::query()->where('is_active', true)->where('approval_level', $level);
        if (! in_array($level, LeaveApprovalWorkflow::APPROVER_LEVELS, true)) {
            return $query->whereRaw('1 = 0');
        }
        $departmentId = $request->user?->department_id;

        // Every approval stage must stay within the requester's department.
        if (! $departmentId) {
            return $query->whereRaw('1 = 0');
        }
        $query->where('department_id', $departmentId);

        if ($this->isOfficeScopedLevel($level)) {
            if (! $request->office_id) {
                return $query->whereRaw('1 = 0');
            }
            $query->where('office_id', $request->office_id);
        }

        return $query;
    }

    public function actionableRequests(User $approver): Builder
    {
        $query = LeaveRequest::query()->where('status', 'Menunggu');
        $level = $approver->approval_level;

        if (! $level) {
            return $query->whereRaw('1 = 0');
        }
        if (! in_array($level, LeaveApprovalWorkflow::APPROVER_LEVELS, true)) {
            return $query->whereRaw('1 = 0');
        }

        $query->whereHas('approvals', fn (Builder $approval) => $approval
            ->whereColumn('leave_request_approvals.sequence', 'leave_requests.current_approval_step')
            ->where('leave_request_approvals.approver_user_id', $approver->id)
            ->where('leave_request_approvals.approver_level', $level)
            ->where('leave_request_approvals.status', 'Menunggu'));

        if (! $approver->department_id) {
            return $query->whereRaw('1 = 0');
        }
        $query->whereHas('user', fn (Builder $user) => $user->where('department_id', $approver->department_id));

        if ($this->isOfficeScopedLevel($level)) {
            if (! $approver->office_id) {
                return $query->whereRaw('1 = 0');
            }
            $query->where('office_id', $approver->office_id);
        }

        return $query->where('user_id', '!=', $approver->id);
    }

    public function historyRequests(User $approver): Builder
    {
        $query = LeaveRequest::query()->whereHas('approvals', fn (Builder $approval) => $approval->where('reviewed_by', $approver->id));

        if (! $approver->department_id) {
            return $query->whereRaw('1 = 0');
        }

        $query->whereHas('user', fn (Builder $user) => $user->where('department_id', $approver->department_id));
        if ($this->isOfficeScopedLevel((string) $approver->approval_level)) {
            if (! $approver->office_id) {
                return $query->whereRaw('1 = 0');
            }
            $query->where('office_id', $approver->office_id);
        }

        return $query;
    }

    public static function label(string $level): string
    {
        return LeaveApprovalWorkflow::LEVELS[$level] ?? $level;
    }

    private function isOfficeScopedLevel(string $level): bool
    {
        foreach (LeaveApprovalWorkflow::OFFICE_SCOPED_LEVEL_PREFIXES as $prefix) {
            if (str_starts_with($level, $prefix)) {
                return true;
            }
        }

        return false;
    }
}

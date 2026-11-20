<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $requests = DB::table('leave_requests')
            ->join('users as requester', 'requester.id', '=', 'leave_requests.user_id')
            ->where('leave_requests.status', 'Menunggu')
            ->select('leave_requests.id', 'leave_requests.office_id', 'requester.department_id')
            ->get();

        foreach ($requests as $request) {
            if (! $request->department_id) {
                continue;
            }

            $stages = DB::table('leave_request_approvals')
                ->where('leave_request_id', $request->id)
                ->whereNull('approver_user_id')
                ->get(['id', 'approver_level']);

            foreach ($stages as $stage) {
                if (! in_array($stage->approver_level, ['supervisor', 'hod', 'director', 'spbu_supervisor', 'spbu_manager'], true)) {
                    continue;
                }

                $approvers = DB::table('users')
                    ->where('is_active', true)
                    ->where('approval_level', $stage->approver_level)
                    ->where('department_id', $request->department_id);

                if (str_starts_with($stage->approver_level, 'spbu_')) {
                    $approvers->where('office_id', $request->office_id);
                }

                $approverId = $approvers->orderBy('id')->value('id');
                if ($approverId) {
                    DB::table('leave_request_approvals')
                        ->where('id', $stage->id)
                        ->whereNull('approver_user_id')
                        ->update(['approver_user_id' => $approverId]);
                }
            }
        }
    }

    public function down(): void
    {
        // Assignment repairs are retained when rolling back this data migration.
    }
};

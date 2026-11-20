<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_request_approvals', function (Blueprint $table): void {
            $table->foreignId('approver_user_id')->nullable()->after('approver_level')->constrained('users')->nullOnDelete();
            $table->index(['approver_user_id', 'status']);
        });

        $stages = DB::table('leave_request_approvals')
            ->join('leave_requests', 'leave_requests.id', '=', 'leave_request_approvals.leave_request_id')
            ->join('users as requester', 'requester.id', '=', 'leave_requests.user_id')
            ->select('leave_request_approvals.id', 'leave_request_approvals.approver_level', 'leave_request_approvals.reviewed_by', 'leave_requests.office_id', 'requester.department_id')
            ->get();

        foreach ($stages as $stage) {
            $approverId = $stage->reviewed_by;
            if (! $approverId) {
                $approvers = DB::table('users')->where('is_active', true)->where('approval_level', $stage->approver_level);
                if (in_array($stage->approver_level, ['supervisor', 'hod'], true)) {
                    $approvers->where('department_id', $stage->department_id);
                }
                if (in_array($stage->approver_level, ['spbu_supervisor', 'spbu_manager'], true)) {
                    $approvers->where('office_id', $stage->office_id);
                }
                $approverId = $approvers->orderBy('id')->value('id');
            }
            DB::table('leave_request_approvals')->where('id', $stage->id)->update(['approver_user_id' => $approverId]);
        }
    }

    public function down(): void
    {
        Schema::table('leave_request_approvals', fn (Blueprint $table) => $table->dropConstrainedForeignId('approver_user_id'));
    }
};

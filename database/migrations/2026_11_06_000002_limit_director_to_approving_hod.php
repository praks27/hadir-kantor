<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            DB::table('leave_approval_workflows')
                ->where('requester_level', 'director')
                ->delete();

            $workflows = DB::table('leave_approval_workflows')
                ->where('requester_level', '!=', 'hod')
                ->get();

            foreach ($workflows as $workflow) {
                $steps = json_decode($workflow->approval_steps, true) ?: [];
                $filteredSteps = array_values(array_filter($steps, fn (string $step): bool => $step !== 'director'));
                if ($steps === $filteredSteps) {
                    continue;
                }

                if ($filteredSteps === [] && $workflow->department_id !== null) {
                    DB::table('leave_approval_workflows')->where('id', $workflow->id)->delete();
                    continue;
                }

                DB::table('leave_approval_workflows')->where('id', $workflow->id)->update([
                    'approval_steps' => json_encode($filteredSteps),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        // Workflow settings are intentionally not restored: they may have been edited after this policy change.
    }
};

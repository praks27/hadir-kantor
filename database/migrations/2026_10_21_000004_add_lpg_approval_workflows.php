<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            ['lpg', 'lpg_operator', ['lpg_supervisor', 'lpg_manager']],
            ['lpg', 'lpg_driver', ['lpg_supervisor', 'lpg_manager']],
            ['lpg', 'lpg_supervisor', ['lpg_manager', 'hod']],
            ['lpg', 'lpg_manager', ['hod', 'director']],
            ['lpg', 'hod', ['director']],
            ['lpg', 'director', ['director']],
        ];

        foreach ($defaults as [$workplaceType, $requesterLevel, $steps]) {
            DB::table('leave_approval_workflows')->updateOrInsert(
                ['workplace_type' => $workplaceType, 'department_id' => null, 'requester_level' => $requesterLevel],
                ['approval_steps' => json_encode($steps), 'created_at' => now(), 'updated_at' => now()],
            );
        }
    }

    public function down(): void
    {
        DB::table('leave_approval_workflows')->where('workplace_type', 'lpg')->delete();
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_approval_workflows', function (Blueprint $table): void {
            $table->dropUnique(['workplace_type', 'requester_level']);
            $table->foreignId('department_id')->nullable()->after('workplace_type')->constrained()->nullOnDelete();
            $table->unique(['workplace_type', 'department_id', 'requester_level'], 'leave_approval_workflows_department_requester_unique');
        });
    }

    public function down(): void
    {
        DB::table('leave_approval_workflows')->whereNotNull('department_id')->delete();

        Schema::table('leave_approval_workflows', function (Blueprint $table): void {
            $table->dropUnique('leave_approval_workflows_department_requester_unique');
            $table->dropConstrainedForeignId('department_id');
            $table->unique(['workplace_type', 'requester_level']);
        });
    }
};

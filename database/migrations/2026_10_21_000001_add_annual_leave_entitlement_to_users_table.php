<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedSmallInteger('annual_leave_entitlement')->default(12);
        });

        Schema::table('leave_requests', function (Blueprint $table): void {
            $table->index(['user_id', 'leave_type', 'status', 'start_date', 'end_date'], 'leave_requests_user_leave_balance_idx');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('annual_leave_entitlement');
        });

        Schema::table('leave_requests', function (Blueprint $table): void {
            $table->dropIndex('leave_requests_user_leave_balance_idx');
        });
    }
};

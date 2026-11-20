<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['attendance_date', 'office_id'], 'attendances_date_office_idx');
            $table->index(['status', 'attendance_date'], 'attendances_status_date_idx');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->index(['start_date', 'office_id'], 'leave_requests_start_office_idx');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex('leave_requests_start_office_idx');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('attendances_date_office_idx');
            $table->dropIndex('attendances_status_date_idx');
        });
    }
};

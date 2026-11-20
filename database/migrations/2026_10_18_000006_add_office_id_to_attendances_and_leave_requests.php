<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('office_id')->nullable()->after('user_id')->constrained('office_settings')->nullOnDelete();
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->foreignId('office_id')->nullable()->after('user_id')->constrained('office_settings')->nullOnDelete();
        });

        $defaultOfficeId = DB::table('office_settings')->orderBy('id')->value('id');
        if ($defaultOfficeId) {
            DB::table('attendances')->whereNull('office_id')->update(['office_id' => $defaultOfficeId]);
            DB::table('leave_requests')->whereNull('office_id')->update(['office_id' => $defaultOfficeId]);
        }
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('office_id');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('office_id');
        });
    }
};

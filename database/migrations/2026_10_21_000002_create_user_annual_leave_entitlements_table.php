<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_annual_leave_entitlements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedSmallInteger('days');
            $table->timestamps();
            $table->unique(['user_id', 'year']);
        });

        DB::table('users')->select('id', 'annual_leave_entitlement')->orderBy('id')->chunkById(500, function ($users): void {
            $now = now();
            $entitlements = $users->map(fn ($user) => [
                'user_id' => $user->id,
                'year' => (int) $now->year,
                'days' => (int) $user->annual_leave_entitlement,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            if ($entitlements) {
                DB::table('user_annual_leave_entitlements')->insert($entitlements);
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('annual_leave_entitlement');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedSmallInteger('annual_leave_entitlement')->default(12);
        });

        DB::table('user_annual_leave_entitlements')
            ->where('year', (int) now()->year)
            ->orderBy('id')
            ->get(['user_id', 'days'])
            ->each(fn ($entitlement) => DB::table('users')->where('id', $entitlement->user_id)->update(['annual_leave_entitlement' => $entitlement->days]));

        Schema::dropIfExists('user_annual_leave_entitlements');
    }
};

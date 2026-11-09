<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('office_settings', function (Blueprint $table): void {
            $table->string('workplace_type', 20)->default('kantor')->after('name');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('approval_level', 40)->nullable()->index()->after('position');
            $table->foreignId('office_id')->nullable()->after('approval_level')->constrained('office_settings')->nullOnDelete();
        });
        DB::table('users')->where('role', 'karyawan')->whereNull('approval_level')->update(['approval_level' => 'staff']);

        Schema::table('leave_requests', function (Blueprint $table): void {
            $table->unsignedTinyInteger('current_approval_step')->default(1)->after('status');
        });

        Schema::create('leave_approval_workflows', function (Blueprint $table): void {
            $table->id();
            $table->string('workplace_type', 20);
            $table->string('requester_level', 40);
            $table->json('approval_steps');
            $table->timestamps();
            $table->unique(['workplace_type', 'requester_level']);
        });

        Schema::create('leave_request_approvals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('leave_request_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('sequence');
            $table->string('approver_level', 40);
            $table->string('status', 20)->default('Menunggu');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['leave_request_id', 'sequence']);
            $table->index(['approver_level', 'status']);
        });

        $defaults = [
            ['kantor', 'staff', ['supervisor', 'hod', 'director']],
            ['kantor', 'supervisor', ['hod', 'director']],
            ['kantor', 'hod', ['director']],
            ['kantor', 'director', ['director']],
            ['spbu', 'spbu_operator', ['spbu_supervisor', 'spbu_manager']],
            ['spbu', 'spbu_supervisor', ['spbu_manager', 'hod']],
            ['spbu', 'spbu_manager', ['hod', 'director']],
            ['spbu', 'hod', ['director']],
            ['spbu', 'director', ['director']],
        ];

        foreach ($defaults as [$workplaceType, $requesterLevel, $steps]) {
            DB::table('leave_approval_workflows')->insert([
                'workplace_type' => $workplaceType,
                'requester_level' => $requesterLevel,
                'approval_steps' => json_encode($steps),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $pendingRequests = DB::table('leave_requests')
            ->join('users', 'users.id', '=', 'leave_requests.user_id')
            ->leftJoin('office_settings', 'office_settings.id', '=', 'leave_requests.office_id')
            ->where('leave_requests.status', 'Menunggu')
            ->selectRaw("leave_requests.id, users.approval_level, COALESCE(office_settings.workplace_type, 'kantor') as workplace_type")
            ->get();
        foreach ($pendingRequests as $pendingRequest) {
            $stepsJson = DB::table('leave_approval_workflows')
                ->where('workplace_type', $pendingRequest->workplace_type)
                ->where('requester_level', $pendingRequest->approval_level ?? 'staff')
                ->value('approval_steps');
            if (! $stepsJson) {
                continue;
            }
            foreach (json_decode($stepsJson, true) as $index => $level) {
                DB::table('leave_request_approvals')->insert([
                    'leave_request_id' => $pendingRequest->id,
                    'sequence' => $index + 1,
                    'approver_level' => $level,
                    'status' => $index === 0 ? 'Menunggu' : 'Belum diproses',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_request_approvals');
        Schema::dropIfExists('leave_approval_workflows');
        Schema::table('leave_requests', fn (Blueprint $table) => $table->dropColumn('current_approval_step'));
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('office_id');
            $table->dropColumn('approval_level');
        });
        Schema::table('office_settings', fn (Blueprint $table) => $table->dropColumn('workplace_type'));
    }
};

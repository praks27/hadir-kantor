<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_access_settings', function (Blueprint $table) {
            $table->id();
            $table->string('scope_type', 16);
            $table->string('scope_key', 64);
            $table->json('permissions');
            $table->timestamps();
            $table->unique(['scope_type', 'scope_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_access_settings');
    }
};

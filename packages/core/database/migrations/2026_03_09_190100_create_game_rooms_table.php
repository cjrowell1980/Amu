<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_module_id')->constrained('game_modules')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->restrictOnDelete();
            $table->string('code', 12)->unique();
            $table->string('name');
            $table->string('visibility', 20)->default('public');
            $table->string('status', 20)->default('waiting');
            $table->unsignedSmallInteger('min_players')->default(1);
            $table->unsignedSmallInteger('max_players')->default(8);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_rooms');
    }
};

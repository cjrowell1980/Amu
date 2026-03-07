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
            $table->string('code', 8)->unique()->comment('Short human-shareable join code');
            $table->string('name', 120)->nullable();
            $table->foreignId('game_id')->constrained('games')->restrictOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->restrictOnDelete();
            $table->enum('visibility', ['public', 'private', 'unlisted'])->default('public');
            $table->enum('status', [
                'waiting',   // in lobby, waiting for players
                'starting',  // countdown / game starting
                'in_game',   // session active
                'finished',  // game over
                'cancelled', // host cancelled
            ])->default('waiting');
            $table->string('password_hash')->nullable()->comment('Null = no password required');
            $table->unsignedSmallInteger('max_players')->nullable()->comment('Override game default if set');
            $table->boolean('allow_spectators')->default(false);
            $table->json('room_config')->nullable()->comment('Game-specific room-level overrides');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'visibility']);
            $table->index('game_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_rooms');
    }
};

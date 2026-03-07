<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_session_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained('game_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->enum('role', ['player', 'spectator'])->default('player');
            $table->unsignedSmallInteger('team_number')->nullable();
            $table->unsignedSmallInteger('seat_number')->nullable();
            $table->enum('connection_status', ['connected', 'disconnected', 'reconnecting'])->default('connected');
            $table->integer('final_rank')->nullable()->comment('Populated when session ends');
            $table->integer('score')->nullable();
            $table->json('result_detail')->nullable()->comment('Per-player result blob from game module');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            $table->unique(['game_session_id', 'user_id']);
            $table->index('game_session_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_session_participants');
    }
};

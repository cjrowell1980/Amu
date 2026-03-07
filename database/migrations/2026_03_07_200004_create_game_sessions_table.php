<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique()->comment('Public-facing session reference');
            $table->foreignId('game_id')->constrained('games')->restrictOnDelete();
            $table->foreignId('game_room_id')->constrained('game_rooms')->restrictOnDelete();
            $table->enum('status', [
                'created',    // session record created, not started
                'starting',   // brief starting phase (countdown)
                'active',     // gameplay in progress
                'paused',     // temporarily halted
                'completed',  // finished naturally
                'abandoned',  // did not finish (disconnects, cancel)
            ])->default('created');
            $table->json('session_config')->nullable()->comment('Resolved config snapshot for this session');
            $table->json('result_summary')->nullable()->comment('Outcome blob written by game module on end');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'game_id']);
            $table->index('game_room_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_sessions');
    }
};

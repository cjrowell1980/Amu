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
            $table->string('uuid')->unique();
            $table->foreignId('game_id')->constrained('games')->restrictOnDelete();
            $table->foreignId('game_room_id')->constrained('game_rooms')->restrictOnDelete();
            // Valid values enforced by App\Enums\SessionStatus
            $table->string('status', 20)->default('created');
            $table->json('session_config')->nullable();
            $table->json('result_summary')->nullable();
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

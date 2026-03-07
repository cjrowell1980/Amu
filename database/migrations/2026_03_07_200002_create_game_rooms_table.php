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
            $table->string('code', 8)->unique();
            $table->string('name', 120)->nullable();
            $table->foreignId('game_id')->constrained('games')->restrictOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->restrictOnDelete();
            // Using string instead of enum for cross-database compatibility
            // Valid values enforced by App\Enums\RoomVisibility
            $table->string('visibility', 20)->default('public');
            // Valid values enforced by App\Enums\RoomStatus
            $table->string('status', 20)->default('waiting');
            $table->string('password_hash')->nullable();
            $table->unsignedSmallInteger('max_players')->nullable();
            $table->boolean('allow_spectators')->default(false);
            $table->json('room_config')->nullable();
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

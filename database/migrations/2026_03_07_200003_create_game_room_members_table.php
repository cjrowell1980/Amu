<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_room_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_room_id')->constrained('game_rooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['host', 'player', 'spectator'])->default('player');
            $table->boolean('is_ready')->default(false);
            $table->unsignedSmallInteger('team_number')->nullable()->comment('Null = no teams');
            $table->unsignedSmallInteger('seat_number')->nullable()->comment('Optional ordered seating');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            $table->unique(['game_room_id', 'user_id']);
            $table->index('game_room_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_room_members');
    }
};

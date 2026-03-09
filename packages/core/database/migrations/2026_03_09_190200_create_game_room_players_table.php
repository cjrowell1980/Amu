<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_room_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_room_id')->constrained('game_rooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('participation', 20)->default('joined');
            $table->string('connection_status', 20)->default('connected');
            $table->unsignedSmallInteger('seat_number')->nullable();
            $table->boolean('is_ready')->default(false);
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            $table->unique(['game_room_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_room_players');
    }
};

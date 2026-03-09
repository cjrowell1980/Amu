<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blackjack_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_room_id')->constrained('game_rooms')->cascadeOnDelete();
            $table->foreignId('game_session_id')->nullable()->constrained('game_sessions')->nullOnDelete();
            $table->json('shoe_state')->nullable();
            $table->unsignedTinyInteger('decks')->default(6);
            $table->boolean('dealer_hits_soft_17')->default(false);
            $table->string('status', 20)->default('open');
            $table->timestamps();

            $table->unique('game_room_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blackjack_tables');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blackjack_hands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blackjack_round_id')->constrained('blackjack_rounds')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('blackjack_seat_id')->constrained('blackjack_seats')->cascadeOnDelete();
            $table->json('cards');
            $table->unsignedSmallInteger('value')->default(0);
            $table->string('status', 20)->default('active');
            $table->boolean('is_blackjack')->default(false);
            $table->boolean('is_bust')->default(false);
            $table->string('outcome', 20)->nullable();
            $table->bigInteger('payout')->default(0);
            $table->timestamps();

            $table->unique(['blackjack_round_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blackjack_hands');
    }
};

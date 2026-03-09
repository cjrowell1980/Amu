<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blackjack_bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blackjack_round_id')->constrained('blackjack_rounds')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->bigInteger('amount');
            $table->string('status', 20)->default('placed');
            $table->bigInteger('payout')->default(0);
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();

            $table->unique(['blackjack_round_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blackjack_bets');
    }
};

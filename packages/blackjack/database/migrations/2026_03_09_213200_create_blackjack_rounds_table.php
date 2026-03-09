<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blackjack_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blackjack_table_id')->constrained('blackjack_tables')->cascadeOnDelete();
            $table->string('status', 20)->default('betting');
            $table->foreignId('current_turn_seat_id')->nullable()->constrained('blackjack_seats')->nullOnDelete();
            $table->json('dealer_cards')->nullable();
            $table->unsignedSmallInteger('dealer_value')->default(0);
            $table->json('outcome_summary')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blackjack_rounds');
    }
};

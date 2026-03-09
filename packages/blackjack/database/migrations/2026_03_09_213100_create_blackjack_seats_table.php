<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blackjack_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blackjack_table_id')->constrained('blackjack_tables')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('seat_number');
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['blackjack_table_id', 'user_id']);
            $table->unique(['blackjack_table_id', 'seat_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blackjack_seats');
    }
};

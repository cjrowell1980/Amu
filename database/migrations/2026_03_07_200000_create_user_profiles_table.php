<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('display_name', 60)->nullable();
            $table->string('avatar_url')->nullable();
            $table->enum('status', ['online', 'away', 'offline', 'in_game'])->default('offline');
            $table->string('country_code', 2)->nullable();
            $table->json('preferences')->nullable()->comment('User UI/gameplay preferences blob');
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};

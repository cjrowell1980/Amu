<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique()->comment('Machine-readable unique identifier');
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->string('module_class')->nullable()->comment('FQCN of the GameModule implementation');
            $table->boolean('enabled')->default(false);
            $table->boolean('supports_teams')->default(false);
            $table->unsignedSmallInteger('min_players')->default(2);
            $table->unsignedSmallInteger('max_players')->default(8);
            $table->json('default_config')->nullable()->comment('Game-specific defaults / rule presets');
            $table->string('version', 20)->nullable()->comment('Module version for compatibility tracking');
            $table->string('thumbnail_url')->nullable();
            $table->unsignedBigInteger('play_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};

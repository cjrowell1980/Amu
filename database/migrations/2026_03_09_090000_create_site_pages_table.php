<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('nav_label', 40);
            $table->string('title', 120);
            $table->string('hero_title', 160);
            $table->text('hero_body');
            $table->longText('body');
            $table->string('meta_description')->nullable();
            $table->string('cta_label', 60)->nullable();
            $table->string('cta_link')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_pages');
    }
};

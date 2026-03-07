<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // Who did it
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // What they did
            $table->string('event', 80)->index();

            // What it was done to (polymorphic)
            $table->nullableMorphs('subject');

            // Extra context
            $table->json('metadata')->nullable();

            // Where from
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['event', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};

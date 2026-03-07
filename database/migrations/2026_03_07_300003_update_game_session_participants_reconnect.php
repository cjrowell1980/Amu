<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_session_participants', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable()
                ->comment('Last heartbeat / activity timestamp')
                ->after('connection_status');

            $table->timestamp('disconnected_at')->nullable()
                ->comment('When the player dropped connection')
                ->after('last_seen_at');

            $table->string('reconnect_token', 64)->nullable()->unique()
                ->comment('Short-lived token for reconnect authentication')
                ->after('disconnected_at');
        });
    }

    public function down(): void
    {
        Schema::table('game_session_participants', function (Blueprint $table) {
            $table->dropColumn(['last_seen_at', 'disconnected_at', 'reconnect_token']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migrate game_rooms.status to the expanded lifecycle values.
     *
     * Old: waiting | starting | in_game | finished | cancelled
     * New: waiting | ready | starting | in_progress | completed | cancelled | closed
     *
     * Note: SQLite does not support ENUM or ALTER COLUMN type changes.
     * We use a string column approach: the PHP RoomStatus enum handles validation.
     * On MySQL/MariaDB the existing ENUM column is widened via raw statement.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE game_rooms MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'waiting'");
        }
        // SQLite / others: the column already stores strings; no structural change needed.

        // Migrate old values to new canonical names
        DB::table('game_rooms')->where('status', 'in_game')->update(['status' => 'in_progress']);
        DB::table('game_rooms')->where('status', 'finished')->update(['status' => 'completed']);
    }

    public function down(): void
    {
        DB::table('game_rooms')->where('status', 'in_progress')->update(['status' => 'in_game']);
        DB::table('game_rooms')->where('status', 'completed')->update(['status' => 'finished']);
        DB::table('game_rooms')->whereIn('status', ['ready', 'closed'])->update(['status' => 'waiting']);
    }
};

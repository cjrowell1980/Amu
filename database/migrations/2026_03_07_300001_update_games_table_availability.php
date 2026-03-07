<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            // Add availability string column to replace the boolean 'enabled'
            $table->string('availability', 20)->default('disabled')
                ->comment('enabled|beta|hidden|disabled')
                ->after('module_class');

            // Optional role required to access beta/hidden games
            $table->string('required_role', 80)->nullable()
                ->comment('If set, only users with this role can join')
                ->after('availability');
        });

        // Migrate existing data: enabled=1 → 'enabled', enabled=0 → 'disabled'
        DB::table('games')->where('enabled', 1)->update(['availability' => 'enabled']);
        DB::table('games')->where('enabled', 0)->update(['availability' => 'disabled']);

        // Drop the old boolean column
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('enabled');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->boolean('enabled')->default(false)->after('module_class');
        });

        DB::table('games')->where('availability', 'enabled')->update(['enabled' => 1]);
        DB::table('games')->where('availability', '!=', 'enabled')->update(['enabled' => 0]);

        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn(['availability', 'required_role']);
        });
    }
};

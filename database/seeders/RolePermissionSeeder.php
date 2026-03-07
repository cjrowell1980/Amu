<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions.
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Platform management
            'manage games',
            'manage users',
            'manage rooms',
            'view telescope',
            'view horizon',

            // Room permissions
            'create rooms',
            'moderate rooms',
            'force close rooms',

            // Session permissions
            'view sessions',
            'force end sessions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // player — basic platform participant
        $player = Role::firstOrCreate(['name' => 'player']);
        $player->syncPermissions(['create rooms']);

        // moderator — can moderate rooms and review sessions
        $moderator = Role::firstOrCreate(['name' => 'moderator']);
        $moderator->syncPermissions([
            'create rooms',
            'moderate rooms',
            'force close rooms',
            'view sessions',
            'force end sessions',
        ]);

        // operator — can manage game registry and view tooling
        $operator = Role::firstOrCreate(['name' => 'operator']);
        $operator->syncPermissions([
            'create rooms',
            'manage games',
            'moderate rooms',
            'force close rooms',
            'view sessions',
            'force end sessions',
            'view telescope',
            'view horizon',
        ]);

        // admin — all permissions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());
    }
}

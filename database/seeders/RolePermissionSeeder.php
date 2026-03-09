<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'manage platform',
            'manage modules',
            'manage rooms',
            'manage sessions',
            'manage wallets',
            'host rooms',
            'join rooms',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        Role::firstOrCreate(['name' => 'player'])->syncPermissions(['join rooms']);

        Role::firstOrCreate(['name' => 'host'])->syncPermissions([
            'host rooms',
            'join rooms',
        ]);

        Role::firstOrCreate(['name' => 'admin'])->syncPermissions(Permission::all());
    }
}

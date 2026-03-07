<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');
        $adminPassword = env('ADMIN_PASSWORD', 'password');

        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Platform Admin',
                'password' => bcrypt($adminPassword),
            ],
        );

        if (! $admin->profile) {
            UserProfile::create([
                'user_id' => $admin->id,
                'display_name' => 'Admin',
                'status' => 'online',
            ]);
        }

        $admin->syncRoles(['admin']);

        // Create an operator test account as well.
        $operator = User::firstOrCreate(
            ['email' => 'operator@example.com'],
            [
                'name' => 'Platform Operator',
                'password' => bcrypt('password'),
            ],
        );

        if (! $operator->profile) {
            UserProfile::create([
                'user_id' => $operator->id,
                'display_name' => 'Operator',
                'status' => 'online',
            ]);
        }

        $operator->syncRoles(['operator']);

        $this->command->info('Admin: ' . $adminEmail . ' / ' . $adminPassword);
        $this->command->info('Operator: operator@example.com / password');
    }
}

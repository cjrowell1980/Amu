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

        $this->command?->info('Admin: '.$adminEmail.' / '.$adminPassword);
    }
}

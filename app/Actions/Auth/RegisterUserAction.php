<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\DB;

class RegisterUserAction
{
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            UserProfile::create([
                'user_id' => $user->id,
                'display_name' => $data['display_name'] ?? $data['name'],
            ]);

            $user->assignRole('player');

            return $user->load('profile');
        });
    }
}

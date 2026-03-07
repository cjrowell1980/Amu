<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserProfile>
 */
class UserProfileFactory extends Factory
{
    protected $model = UserProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'display_name' => $this->faker->userName(),
            'avatar_url' => null,
            'status' => $this->faker->randomElement(['online', 'offline', 'away']),
            'country_code' => $this->faker->countryCode(),
            'preferences' => null,
        ];
    }
}

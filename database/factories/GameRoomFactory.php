<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GameRoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(Str::random(6)),
            'name' => $this->faker->words(3, true),
            'game_id' => Game::factory(),
            'host_user_id' => User::factory(),
            'visibility' => 'public',
            'status' => 'waiting',
            'password_hash' => null,
            'max_players' => null,
            'allow_spectators' => false,
            'room_config' => null,
        ];
    }

    public function private(): static
    {
        return $this->state(['visibility' => 'private']);
    }

    public function withPassword(string $password = 'secret'): static
    {
        return $this->state(['password_hash' => bcrypt($password)]);
    }

    public function inGame(): static
    {
        return $this->state(['status' => 'in_game']);
    }
}

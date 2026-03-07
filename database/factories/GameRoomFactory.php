<?php

namespace Database\Factories;

use App\Enums\RoomStatus;
use App\Enums\RoomVisibility;
use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GameRoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code'             => strtoupper(Str::random(6)),
            'name'             => $this->faker->words(3, true),
            'game_id'          => Game::factory(),
            'host_user_id'     => User::factory(),
            'visibility'       => RoomVisibility::Public->value,
            'status'           => RoomStatus::Waiting->value,
            'password_hash'    => null,
            'max_players'      => null,
            'allow_spectators' => false,
            'room_config'      => null,
        ];
    }

    public function private(): static
    {
        return $this->state(['visibility' => RoomVisibility::Private->value]);
    }

    public function withPassword(string $password = 'secret'): static
    {
        return $this->state([
            'visibility'    => RoomVisibility::Private->value,
            'password_hash' => bcrypt($password),
        ]);
    }

    public function ready(): static
    {
        return $this->state(['status' => RoomStatus::Ready->value]);
    }

    public function inProgress(): static
    {
        return $this->state(['status' => RoomStatus::InProgress->value]);
    }

    public function completed(): static
    {
        return $this->state(['status' => RoomStatus::Completed->value]);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => RoomStatus::Cancelled->value]);
    }
}

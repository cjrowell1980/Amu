<?php

namespace Database\Factories;

use App\Enums\SessionStatus;
use App\Models\Game;
use App\Models\GameRoom;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GameSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid'           => (string) Str::uuid(),
            'game_id'        => Game::factory(),
            'game_room_id'   => GameRoom::factory(),
            'status'         => SessionStatus::Created->value,
            'session_config' => [],
            'result_summary' => null,
            'started_at'     => null,
            'ended_at'       => null,
        ];
    }

    public function active(): static
    {
        return $this->state([
            'status'     => SessionStatus::Active->value,
            'started_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state([
            'status'     => SessionStatus::Completed->value,
            'started_at' => now()->subHour(),
            'ended_at'   => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status'   => SessionStatus::Cancelled->value,
            'ended_at' => now(),
        ]);
    }
}

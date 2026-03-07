<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $games = [
            [
                'slug' => 'example-game',
                'name' => 'Example Game',
                'description' => 'A stub game module demonstrating the platform contract. Not a real game.',
                'module_class' => \App\Modules\ExampleGame\ExampleGameModule::class,
                'enabled' => true,
                'supports_teams' => false,
                'min_players' => 2,
                'max_players' => 8,
                'version' => '1.0.0',
                'default_config' => ['rounds' => 3, 'turn_timeout_seconds' => 30],
            ],
            [
                'slug' => 'poker',
                'name' => 'Poker',
                'description' => 'Texas Hold\'em poker. Module not yet implemented.',
                'module_class' => null,
                'enabled' => false,
                'supports_teams' => false,
                'min_players' => 2,
                'max_players' => 9,
                'version' => null,
                'default_config' => ['starting_chips' => 1000, 'blind' => 10],
            ],
            [
                'slug' => 'blackjack',
                'name' => 'Blackjack',
                'description' => 'Classic blackjack against the dealer. Module not yet implemented.',
                'module_class' => null,
                'enabled' => false,
                'supports_teams' => false,
                'min_players' => 1,
                'max_players' => 7,
                'version' => null,
                'default_config' => ['decks' => 6],
            ],
            [
                'slug' => 'trivia',
                'name' => 'Trivia',
                'description' => 'Multiplayer trivia quiz. Module not yet implemented.',
                'module_class' => null,
                'enabled' => false,
                'supports_teams' => true,
                'min_players' => 2,
                'max_players' => 20,
                'version' => null,
                'default_config' => ['rounds' => 10, 'time_per_question' => 20],
            ],
        ];

        foreach ($games as $game) {
            Game::updateOrCreate(
                ['slug' => $game['slug']],
                $game,
            );
        }
    }
}

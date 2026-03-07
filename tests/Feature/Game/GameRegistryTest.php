<?php

namespace Tests\Feature\Game;

use App\Enums\GameAvailability;
use App\Models\Game;
use App\Models\User;
use Database\Seeders\GameSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameRegistryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(GameSeeder::class);
    }

    /** @test */
    public function enabled_games_are_listed_for_regular_players()
    {
        $user = User::factory()->create();
        $user->assignRole('player');

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/games');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['slug', 'name', 'availability', 'min_players', 'max_players'],
                ],
            ]);

        foreach ($response->json('data') as $game) {
            $this->assertEquals(GameAvailability::Enabled->value, $game['availability']);
        }
    }

    /** @test */
    public function disabled_games_are_not_shown_to_players()
    {
        $user = User::factory()->create();
        $user->assignRole('player');

        $disabledGame = Game::factory()->disabled()->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/games');

        $ids = collect($response->json('data'))->pluck('slug');
        $this->assertNotContains($disabledGame->slug, $ids);
    }

    /** @test */
    public function beta_games_visible_to_beta_testers()
    {
        $betaGame = Game::factory()->beta()->create();
        $betaUser = User::factory()->create();
        $betaUser->assignRole('beta_tester');

        $response = $this->actingAs($betaUser, 'sanctum')->getJson('/api/v1/games');

        $slugs = collect($response->json('data'))->pluck('slug');
        $this->assertContains($betaGame->slug, $slugs);
    }

    /** @test */
    public function beta_games_not_visible_to_regular_players()
    {
        $betaGame = Game::factory()->beta()->create();
        $player   = User::factory()->create();
        $player->assignRole('player');

        $response = $this->actingAs($player, 'sanctum')->getJson('/api/v1/games');

        $slugs = collect($response->json('data'))->pluck('slug');
        $this->assertNotContains($betaGame->slug, $slugs);
    }

    /** @test */
    public function operators_see_non_disabled_games()
    {
        $hiddenGame   = Game::factory()->hidden()->create();
        $disabledGame = Game::factory()->disabled()->create();
        $operator     = User::factory()->create();
        $operator->assignRole('operator');

        $response = $this->actingAs($operator, 'sanctum')->getJson('/api/v1/games');
        $slugs    = collect($response->json('data'))->pluck('slug');

        $this->assertContains($hiddenGame->slug, $slugs);
        $this->assertNotContains($disabledGame->slug, $slugs);
    }

    /** @test */
    public function guests_cannot_list_games()
    {
        $this->getJson('/api/v1/games')->assertUnauthorized();
    }

    /** @test */
    public function game_registry_resolves_example_module()
    {
        $registry = app(\App\Services\GameRegistryService::class);

        $this->assertTrue($registry->has('example-game'));

        $module = $registry->resolve('example-game');
        $this->assertInstanceOf(\App\Contracts\GameModuleInterface::class, $module);
        $this->assertEquals('example-game', $module->getSlug());
    }

    /** @test */
    public function game_registry_throws_for_unknown_slug()
    {
        $registry = app(\App\Services\GameRegistryService::class);

        $this->expectException(\InvalidArgumentException::class);
        $registry->resolve('non-existent-game');
    }

    /** @test */
    public function registering_duplicate_slug_throws()
    {
        $registry = app(\App\Services\GameRegistryService::class);

        $this->expectException(\InvalidArgumentException::class);
        // example-game is already registered in AppServiceProvider
        $registry->register(new \App\Modules\ExampleGame\ExampleGameModule());
    }
}

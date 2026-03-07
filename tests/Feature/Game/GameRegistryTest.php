<?php

namespace Tests\Feature\Game;

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

    public function test_enabled_games_are_listed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/games');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'slug', 'name', 'enabled', 'min_players', 'max_players'],
                ],
            ]);

        // Only enabled games should appear.
        $responseData = $response->json('data');
        foreach ($responseData as $game) {
            $this->assertTrue($game['enabled']);
        }
    }

    public function test_disabled_games_are_not_in_listing(): void
    {
        $user = User::factory()->create();

        $disabledGame = Game::factory()->disabled()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/games');

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertNotContains($disabledGame->id, $ids);
    }

    public function test_guests_cannot_list_games(): void
    {
        $this->getJson('/api/v1/games')
            ->assertUnauthorized();
    }

    public function test_game_registry_service_resolves_module(): void
    {
        $registry = app(\App\Services\GameRegistryService::class);

        $this->assertTrue($registry->has('example-game'));

        $module = $registry->get('example-game');
        $this->assertInstanceOf(\App\Contracts\GameModuleInterface::class, $module);
        $this->assertEquals('example-game', $module->getSlug());
    }

    public function test_game_registry_service_throws_for_unknown_slug(): void
    {
        $registry = app(\App\Services\GameRegistryService::class);

        $this->expectException(\InvalidArgumentException::class);
        $registry->get('non-existent-game');
    }
}

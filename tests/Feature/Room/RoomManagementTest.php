<?php

namespace Tests\Feature\Room;

use Amu\Core\Contracts\GameModule;
use Amu\Core\Contracts\ModuleRegistry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $host;
    private User $player;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->host = User::factory()->create();
        $this->host->assignRole('host');

        $this->player = User::factory()->create();
        $this->player->assignRole('player');

        $registry = app(ModuleRegistry::class);
        $module = $registry->register(new class implements GameModule {
            public function slug(): string { return 'blackjack-placeholder'; }
            public function name(): string { return 'Blackjack Placeholder'; }
            public function version(): string { return '0.1.0'; }
            public function description(): ?string { return 'Placeholder only.'; }
            public function settings(): array { return []; }
            public function boot(ModuleRegistry $registry): void {}
        });
        $registry->enable($module);
    }

    public function test_host_can_create_room_and_player_can_join_leave_and_toggle_ready(): void
    {
        $create = $this->actingAs($this->host, 'sanctum')->postJson('/api/v1/rooms', [
            'game_module_slug' => 'blackjack-placeholder',
            'name' => 'Table One',
            'visibility' => 'public',
            'min_players' => 1,
            'max_players' => 5,
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.name', 'Table One')
            ->assertJsonPath('data.module.slug', 'blackjack-placeholder');

        $roomId = $create->json('data.id');

        $this->actingAs($this->player, 'sanctum')
            ->postJson("/api/v1/rooms/{$roomId}/join", ['participation' => 'seated'])
            ->assertOk()
            ->assertJsonPath('data.participants.1.participation', 'seated');

        $this->actingAs($this->player, 'sanctum')
            ->postJson("/api/v1/rooms/{$roomId}/ready", ['is_ready' => true])
            ->assertOk()
            ->assertJsonPath('data.participants.1.is_ready', true);

        $this->actingAs($this->player, 'sanctum')
            ->deleteJson("/api/v1/rooms/{$roomId}/leave")
            ->assertOk();

        $this->assertDatabaseHas('game_room_players', [
            'game_room_id' => $roomId,
            'user_id' => $this->player->id,
        ]);
    }
}

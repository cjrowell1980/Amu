<?php

namespace Tests\Feature\Lifecycle;

use Amu\Core\Contracts\GameModule;
use Amu\Core\Contracts\ModuleRegistry;
use Amu\Core\Contracts\SessionManager;
use Amu\Core\Enums\SessionStatus;
use Amu\Core\Models\GameRoom;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class SessionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $registry = app(ModuleRegistry::class);
        $module = $registry->register(new class implements GameModule {
            public function slug(): string { return 'session-test'; }
            public function name(): string { return 'Session Test'; }
            public function version(): string { return '0.1.0'; }
            public function description(): ?string { return null; }
            public function settings(): array { return []; }
            public function boot(ModuleRegistry $registry): void {}
        });
        $registry->enable($module);
    }

    public function test_session_transitions_follow_generic_lifecycle(): void
    {
        $host = User::factory()->create();
        $module = app(ModuleRegistry::class)->findBySlug('session-test');

        $room = GameRoom::query()->create([
            'game_module_id' => $module->id,
            'host_user_id' => $host->id,
            'code' => 'ROOM01',
            'name' => 'Lifecycle Room',
            'visibility' => 'public',
            'status' => 'waiting',
            'min_players' => 1,
            'max_players' => 4,
        ]);

        $sessions = app(SessionManager::class);
        $session = $sessions->create($room, ['status' => SessionStatus::Pending]);

        $sessions->transition($session, SessionStatus::Waiting);
        $sessions->transition($session->fresh(), SessionStatus::Active);
        $sessions->transition($session->fresh(), SessionStatus::Paused);
        $sessions->transition($session->fresh(), SessionStatus::Finished);

        $this->assertDatabaseHas('game_sessions', [
            'id' => $session->id,
            'status' => SessionStatus::Finished->value,
        ]);
    }

    public function test_invalid_session_transition_throws(): void
    {
        $host = User::factory()->create();
        $module = app(ModuleRegistry::class)->findBySlug('session-test');

        $room = GameRoom::query()->create([
            'game_module_id' => $module->id,
            'host_user_id' => $host->id,
            'code' => 'ROOM02',
            'name' => 'Invalid Lifecycle Room',
            'visibility' => 'public',
            'status' => 'waiting',
            'min_players' => 1,
            'max_players' => 4,
        ]);

        $session = app(SessionManager::class)->create($room, ['status' => SessionStatus::Pending]);

        $this->expectException(RuntimeException::class);
        app(SessionManager::class)->transition($session, SessionStatus::Finished);
    }
}

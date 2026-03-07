<?php

namespace Tests\Feature\Lifecycle;

use App\Enums\ConnectionStatus;
use App\Enums\GameAvailability;
use App\Enums\RoomMemberRole;
use App\Enums\RoomStatus;
use App\Enums\SessionStatus;
use App\Models\Game;
use App\Models\GameRoom;
use App\Models\GameRoomMember;
use App\Models\GameSession;
use App\Models\GameSessionParticipant;
use App\Models\User;
use App\Services\SessionLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class SessionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private Game $game;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->game = Game::factory()->create([
            'availability' => GameAvailability::Enabled->value,
            'module_class' => \App\Modules\ExampleGame\ExampleGameModule::class,
            'min_players'  => 2,
            'max_players'  => 4,
        ]);
    }

    private function makeSession(array $sessionAttrs = []): GameSession
    {
        $host = User::factory()->create();
        $room = GameRoom::factory()->create([
            'game_id'      => $this->game->id,
            'host_user_id' => $host->id,
            'status'       => RoomStatus::Ready,
        ]);

        $session = GameSession::factory()->create(array_merge([
            'game_id'      => $this->game->id,
            'game_room_id' => $room->id,
            'status'       => SessionStatus::Created,
        ], $sessionAttrs));

        // Add two participants
        foreach (User::factory(2)->create() as $user) {
            GameSessionParticipant::create([
                'game_session_id'  => $session->id,
                'user_id'          => $user->id,
                'role'             => 'player',
                'connection_status'=> ConnectionStatus::Connected,
                'joined_at'        => now(),
            ]);
        }

        return $session;
    }

    // ── Valid transitions ────────────────────────────────────────────────────

    /** @test */
    public function session_can_transition_created_to_starting()
    {
        $this->assertTrue(SessionStatus::Created->canTransitionTo(SessionStatus::Starting));
    }

    /** @test */
    public function session_can_transition_active_to_completed()
    {
        $this->assertTrue(SessionStatus::Active->canTransitionTo(SessionStatus::Completed));
    }

    /** @test */
    public function session_can_transition_active_to_paused()
    {
        $this->assertTrue(SessionStatus::Active->canTransitionTo(SessionStatus::Paused));
    }

    // ── Invalid transitions ──────────────────────────────────────────────────

    /** @test */
    public function completed_session_has_no_valid_transitions()
    {
        $this->assertEmpty(SessionStatus::Completed->allowedTransitions());
    }

    /** @test */
    public function abandoned_is_terminal()
    {
        $this->assertTrue(SessionStatus::Abandoned->isTerminal());
    }

    // ── Service methods ──────────────────────────────────────────────────────

    /** @test */
    public function lifecycle_service_can_start_a_created_session()
    {
        $session = $this->makeSession(['status' => SessionStatus::Created]);

        app(SessionLifecycleService::class)->startSession($session);

        $fresh = $session->fresh();
        $this->assertEquals(SessionStatus::Active, $fresh->status);
        $this->assertNotNull($fresh->started_at);
    }

    /** @test */
    public function lifecycle_service_can_complete_an_active_session()
    {
        $session = $this->makeSession(['status' => SessionStatus::Active]);

        app(SessionLifecycleService::class)->completeSession($session, ['winner' => null]);

        $fresh = $session->fresh();
        $this->assertEquals(SessionStatus::Completed, $fresh->status);
        $this->assertNotNull($fresh->ended_at);
    }

    /** @test */
    public function lifecycle_service_can_cancel_any_non_terminal_session()
    {
        $session = $this->makeSession(['status' => SessionStatus::Active]);

        app(SessionLifecycleService::class)->cancelSession($session, 'test');

        $this->assertEquals(SessionStatus::Cancelled, $session->fresh()->status);
    }

    /** @test */
    public function cannot_start_a_terminal_session()
    {
        $session = $this->makeSession(['status' => SessionStatus::Completed]);

        $this->expectException(RuntimeException::class);
        app(SessionLifecycleService::class)->startSession($session);
    }

    // ── Reconnect / disconnect ────────────────────────────────────────────────

    /** @test */
    public function can_mark_participant_as_disconnected()
    {
        $session     = $this->makeSession(['status' => SessionStatus::Active]);
        $participant = $session->participants()->first();

        app(SessionLifecycleService::class)->markDisconnected($participant);

        $fresh = $participant->fresh();
        $this->assertEquals(ConnectionStatus::Disconnected, $fresh->connection_status);
        $this->assertNotNull($fresh->disconnected_at);
        $this->assertNotNull($fresh->reconnect_token);
    }

    /** @test */
    public function can_reconnect_with_valid_token()
    {
        $session     = $this->makeSession(['status' => SessionStatus::Active]);
        $participant = $session->participants()->first();

        $service = app(SessionLifecycleService::class);
        $service->markDisconnected($participant);

        $token    = $participant->fresh()->reconnect_token;
        $found    = $service->findByReconnectToken($token);

        $this->assertNotNull($found);
        $this->assertEquals($participant->id, $found->id);

        $service->markReconnected($found);

        $fresh = $found->fresh();
        $this->assertEquals(ConnectionStatus::Connected, $fresh->connection_status);
        $this->assertNull($fresh->reconnect_token);
    }
}

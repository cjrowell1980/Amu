<?php

namespace Tests\Feature\Session;

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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionApiTest extends TestCase
{
    use RefreshDatabase;

    private Game $game;
    private User $host;
    private GameRoom $room;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->game = Game::factory()->create([
            'slug'         => 'example-game',
            'availability' => GameAvailability::Enabled->value,
            'module_class' => \App\Modules\ExampleGame\ExampleGameModule::class,
            'min_players'  => 2,
            'max_players'  => 4,
        ]);

        $this->host = User::factory()->create();
        $this->host->assignRole('player');

        $this->room = GameRoom::factory()->create([
            'game_id'      => $this->game->id,
            'host_user_id' => $this->host->id,
            'status'       => RoomStatus::Ready,
        ]);

        GameRoomMember::create([
            'game_room_id' => $this->room->id,
            'user_id'      => $this->host->id,
            'role'         => RoomMemberRole::Host,
            'is_ready'     => true,
            'joined_at'    => now(),
        ]);
    }

    private function makeActiveSession(): GameSession
    {
        $session = GameSession::factory()->create([
            'game_id'        => $this->game->id,
            'game_room_id'   => $this->room->id,
            'status'         => SessionStatus::Active,
            'started_at'     => now(),
            'session_config' => ['rounds' => 3, 'current_round' => 1, 'scores' => []],
        ]);

        GameSessionParticipant::create([
            'game_session_id'  => $session->id,
            'user_id'          => $this->host->id,
            'role'             => 'player',
            'connection_status'=> ConnectionStatus::Connected,
            'joined_at'        => now(),
        ]);

        return $session;
    }

    /** @test */
    public function host_can_view_session_details()
    {
        $session = $this->makeActiveSession();

        $this->actingAs($this->host)
            ->getJson("/api/v1/sessions/{$session->uuid}")
            ->assertOk()
            ->assertJsonPath('data.uuid', $session->uuid)
            ->assertJsonPath('data.status', 'active');
    }

    /** @test */
    public function non_participant_cannot_view_session()
    {
        $session  = $this->makeActiveSession();
        $stranger = User::factory()->create();
        $stranger->assignRole('player');

        $this->actingAs($stranger)
            ->getJson("/api/v1/sessions/{$session->uuid}")
            ->assertForbidden();
    }

    /** @test */
    public function participant_can_fetch_public_state()
    {
        $session = $this->makeActiveSession();

        $this->actingAs($this->host)
            ->getJson("/api/v1/sessions/{$session->uuid}/state")
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    /** @test */
    public function participant_can_submit_action()
    {
        $session = $this->makeActiveSession();

        $this->actingAs($this->host)
            ->postJson("/api/v1/sessions/{$session->uuid}/action", [
                'type' => 'increment_score',
            ])
            ->assertOk()
            ->assertJsonPath('data.acknowledged', true);
    }

    /** @test */
    public function non_participant_cannot_submit_action()
    {
        $session  = $this->makeActiveSession();
        $stranger = User::factory()->create();
        $stranger->assignRole('player');

        $this->actingAs($stranger)
            ->postJson("/api/v1/sessions/{$session->uuid}/action", ['type' => 'do_something'])
            ->assertForbidden();
    }

    /** @test */
    public function action_rejected_when_session_is_not_active()
    {
        $session = GameSession::factory()->create([
            'game_id'      => $this->game->id,
            'game_room_id' => $this->room->id,
            'status'       => SessionStatus::Completed,
        ]);

        GameSessionParticipant::create([
            'game_session_id'  => $session->id,
            'user_id'          => $this->host->id,
            'role'             => 'player',
            'connection_status'=> ConnectionStatus::Connected,
            'joined_at'        => now(),
        ]);

        $this->actingAs($this->host)
            ->postJson("/api/v1/sessions/{$session->uuid}/action", ['type' => 'do_something'])
            ->assertConflict();
    }

    /** @test */
    public function reconnect_with_valid_token_returns_session()
    {
        $session     = $this->makeActiveSession();
        $participant = $session->participants()->first();
        $participant->update([
            'connection_status' => ConnectionStatus::Disconnected->value,
            'reconnect_token'   => str_repeat('a', 64),
            'disconnected_at'   => now(),
        ]);

        $this->actingAs($this->host)
            ->postJson('/api/v1/sessions/reconnect', ['token' => str_repeat('a', 64)])
            ->assertOk()
            ->assertJsonPath('data.uuid', $session->uuid);
    }
}

<?php

namespace Tests\Feature\Lifecycle;

use App\Enums\GameAvailability;
use App\Enums\RoomMemberRole;
use App\Enums\RoomStatus;
use App\Models\Game;
use App\Models\GameRoom;
use App\Models\GameRoomMember;
use App\Models\User;
use App\Services\RoomLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class RoomLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private Game $game;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->game = Game::factory()->create([
            'availability' => GameAvailability::Enabled->value,
            'min_players'  => 2,
            'max_players'  => 4,
            'module_class' => \App\Modules\ExampleGame\ExampleGameModule::class,
        ]);
    }

    private function makeRoom(User $host, array $attrs = []): GameRoom
    {
        $room = GameRoom::factory()->create(array_merge([
            'game_id'      => $this->game->id,
            'host_user_id' => $host->id,
            'status'       => RoomStatus::Waiting,
        ], $attrs));

        GameRoomMember::create([
            'game_room_id' => $room->id,
            'user_id'      => $host->id,
            'role'         => RoomMemberRole::Host,
            'is_ready'     => true,
            'joined_at'    => now(),
        ]);

        return $room;
    }

    // ── Valid transitions ────────────────────────────────────────────────────

    /** @test */
    public function waiting_can_transition_to_ready()
    {
        $room = GameRoom::factory()->create(['status' => RoomStatus::Waiting, 'game_id' => $this->game->id]);
        $this->assertTrue($room->status->canTransitionTo(RoomStatus::Ready));
    }

    /** @test */
    public function ready_can_transition_to_starting()
    {
        $room = GameRoom::factory()->create(['status' => RoomStatus::Ready, 'game_id' => $this->game->id]);
        $this->assertTrue($room->status->canTransitionTo(RoomStatus::Starting));
    }

    /** @test */
    public function in_progress_can_transition_to_completed()
    {
        $room = GameRoom::factory()->create(['status' => RoomStatus::InProgress, 'game_id' => $this->game->id]);
        $this->assertTrue($room->status->canTransitionTo(RoomStatus::Completed));
    }

    // ── Invalid transitions ──────────────────────────────────────────────────

    /** @test */
    public function completed_cannot_transition_to_waiting()
    {
        $room = GameRoom::factory()->create(['status' => RoomStatus::Completed, 'game_id' => $this->game->id]);
        $this->assertFalse($room->status->canTransitionTo(RoomStatus::Waiting));
    }

    /** @test */
    public function cancelled_room_has_no_valid_transitions()
    {
        $room = GameRoom::factory()->create(['status' => RoomStatus::Cancelled, 'game_id' => $this->game->id]);
        $this->assertEmpty($room->status->allowedTransitions());
    }

    // ── Close / cancel ───────────────────────────────────────────────────────

    /** @test */
    public function lifecycle_service_can_close_a_waiting_room()
    {
        $host = User::factory()->create();
        $room = $this->makeRoom($host);

        app(RoomLifecycleService::class)->closeRoom($room);

        $this->assertEquals(RoomStatus::Closed, $room->fresh()->status);
        $this->assertNotNull(GameRoomMember::where('game_room_id', $room->id)->first()->left_at);
    }

    /** @test */
    public function lifecycle_service_can_cancel_a_waiting_room()
    {
        $host = User::factory()->create();
        $room = $this->makeRoom($host);

        app(RoomLifecycleService::class)->cancelRoom($room);

        $this->assertEquals(RoomStatus::Cancelled, $room->fresh()->status);
    }

    /** @test */
    public function cannot_close_a_terminal_room()
    {
        $room = GameRoom::factory()->create([
            'status'  => RoomStatus::Completed,
            'game_id' => $this->game->id,
        ]);

        $this->expectException(RuntimeException::class);
        app(RoomLifecycleService::class)->closeRoom($room);
    }

    // ── Host transfer ────────────────────────────────────────────────────────

    /** @test */
    public function host_transfer_promotes_new_host()
    {
        $host    = User::factory()->create();
        $player  = User::factory()->create();
        $room    = $this->makeRoom($host);

        GameRoomMember::create([
            'game_room_id' => $room->id,
            'user_id'      => $player->id,
            'role'         => RoomMemberRole::Player,
            'is_ready'     => false,
            'joined_at'    => now(),
        ]);

        app(RoomLifecycleService::class)->transferHost($room, $player);

        $this->assertEquals($player->id, $room->fresh()->host_user_id);

        $newHostMember = GameRoomMember::where('game_room_id', $room->id)
            ->where('user_id', $player->id)
            ->first();
        $this->assertEquals(RoomMemberRole::Host, $newHostMember->role);

        $oldHostMember = GameRoomMember::where('game_room_id', $room->id)
            ->where('user_id', $host->id)
            ->first();
        $this->assertEquals(RoomMemberRole::Player, $oldHostMember->role);
    }

    /** @test */
    public function host_leaving_with_other_players_triggers_transfer()
    {
        $host   = User::factory()->create();
        $player = User::factory()->create();
        $room   = $this->makeRoom($host);

        GameRoomMember::create([
            'game_room_id' => $room->id,
            'user_id'      => $player->id,
            'role'         => RoomMemberRole::Player,
            'is_ready'     => false,
            'joined_at'    => now(),
        ]);

        app(RoomLifecycleService::class)->handleHostLeft($room, $host);

        $this->assertEquals($player->id, $room->fresh()->host_user_id);
    }

    /** @test */
    public function host_leaving_empty_room_cancels_it()
    {
        $host = User::factory()->create();
        $room = $this->makeRoom($host);

        // Remove the host membership (they already left)
        GameRoomMember::where('game_room_id', $room->id)
            ->where('user_id', $host->id)
            ->update(['left_at' => now()]);

        app(RoomLifecycleService::class)->handleHostLeft($room, $host);

        $this->assertEquals(RoomStatus::Cancelled, $room->fresh()->status);
    }

    // ── Ready state ──────────────────────────────────────────────────────────

    /** @test */
    public function room_becomes_ready_when_all_players_are_ready()
    {
        $host   = User::factory()->create();
        $player = User::factory()->create();
        $room   = $this->makeRoom($host); // host is auto-ready

        GameRoomMember::create([
            'game_room_id' => $room->id,
            'user_id'      => $player->id,
            'role'         => RoomMemberRole::Player,
            'is_ready'     => true, // also ready
            'joined_at'    => now(),
        ]);

        $service = app(RoomLifecycleService::class);
        $service->recalculateReadyStatus($room);

        $this->assertEquals(RoomStatus::Ready, $room->fresh()->status);
    }

    /** @test */
    public function room_stays_waiting_when_player_not_ready()
    {
        $host   = User::factory()->create();
        $player = User::factory()->create();
        $room   = $this->makeRoom($host);

        GameRoomMember::create([
            'game_room_id' => $room->id,
            'user_id'      => $player->id,
            'role'         => RoomMemberRole::Player,
            'is_ready'     => false, // not ready
            'joined_at'    => now(),
        ]);

        app(RoomLifecycleService::class)->recalculateReadyStatus($room);

        $this->assertEquals(RoomStatus::Waiting, $room->fresh()->status);
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Enums\GameAvailability;
use App\Enums\RoomMemberRole;
use App\Enums\RoomStatus;
use App\Models\Game;
use App\Models\GameRoom;
use App\Models\GameRoomMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoomTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;
    private Game $game;
    private GameRoom $room;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->game = Game::factory()->create(['availability' => GameAvailability::Enabled->value]);

        $host = User::factory()->create();
        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $this->room = GameRoom::factory()->create([
            'game_id'      => $this->game->id,
            'host_user_id' => $host->id,
            'status'       => RoomStatus::Waiting,
        ]);

        GameRoomMember::create([
            'game_room_id' => $this->room->id,
            'user_id'      => $host->id,
            'role'         => RoomMemberRole::Host,
            'is_ready'     => true,
            'joined_at'    => now(),
        ]);
    }

    /** @test */
    public function operator_can_view_rooms_list()
    {
        $this->actingAs($this->operator)
            ->get('/admin/rooms')
            ->assertOk();
    }

    /** @test */
    public function operator_can_view_room_detail()
    {
        $this->actingAs($this->operator)
            ->get("/admin/rooms/{$this->room->id}")
            ->assertOk();
    }

    /** @test */
    public function operator_can_close_a_waiting_room()
    {
        $this->actingAs($this->operator)
            ->post("/admin/rooms/{$this->room->id}/close")
            ->assertRedirect();

        $this->assertEquals(RoomStatus::Closed, $this->room->fresh()->status);
    }

    /** @test */
    public function operator_can_cancel_a_waiting_room()
    {
        $this->actingAs($this->operator)
            ->post("/admin/rooms/{$this->room->id}/cancel")
            ->assertRedirect();

        $this->assertEquals(RoomStatus::Cancelled, $this->room->fresh()->status);
    }

    /** @test */
    public function player_cannot_access_admin_rooms()
    {
        $player = User::factory()->create();
        $player->assignRole('player');

        $this->actingAs($player)
            ->get('/admin/rooms')
            ->assertForbidden();
    }

    /** @test */
    public function operator_cannot_close_already_completed_room()
    {
        $this->room->update(['status' => RoomStatus::Completed]);

        $this->actingAs($this->operator)
            ->post("/admin/rooms/{$this->room->id}/close")
            ->assertRedirect(); // redirects back with error

        // Status should NOT change from Completed
        $this->assertEquals(RoomStatus::Completed, $this->room->fresh()->status);
    }
}

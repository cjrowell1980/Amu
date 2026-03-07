<?php

namespace Tests\Feature\Room;

use App\Models\Game;
use App\Models\GameRoom;
use App\Models\GameRoomMember;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RoomManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Game $game;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->game = Game::factory()->create(['enabled' => true]);
        $this->user = User::factory()->create();
        $this->user->assignRole('player');
    }

    // ── Create Room ───────────────────────────────────────────────────────────

    public function test_authenticated_user_can_create_room(): void
    {
        Event::fake();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/rooms', [
                'game_id' => $this->game->id,
                'name' => 'My Test Room',
                'visibility' => 'public',
            ]);

        $response->assertCreated()
            ->assertJsonPath('code', fn ($v) => strlen($v) === 6)
            ->assertJsonPath('status', 'waiting');

        $this->assertDatabaseHas('game_rooms', [
            'game_id' => $this->game->id,
            'host_user_id' => $this->user->id,
            'status' => 'waiting',
        ]);

        // Host should be added as a member with 'host' role.
        $this->assertDatabaseHas('game_room_members', [
            'user_id' => $this->user->id,
            'role' => 'host',
        ]);
    }

    public function test_cannot_create_room_for_disabled_game(): void
    {
        $disabledGame = Game::factory()->disabled()->create();

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/rooms', ['game_id' => $disabledGame->id])
            ->assertUnprocessable();
    }

    public function test_unauthenticated_user_cannot_create_room(): void
    {
        $this->postJson('/api/v1/rooms', ['game_id' => $this->game->id])
            ->assertUnauthorized();
    }

    public function test_room_creation_requires_valid_game_id(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/rooms', ['game_id' => 99999])
            ->assertUnprocessable();
    }

    // ── List Rooms ────────────────────────────────────────────────────────────

    public function test_public_waiting_rooms_are_listed(): void
    {
        GameRoom::factory()->count(3)->create([
            'game_id' => $this->game->id,
            'visibility' => 'public',
            'status' => 'waiting',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/rooms');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta']);

        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
    }

    // ── Join Room ─────────────────────────────────────────────────────────────

    public function test_user_can_join_a_public_room(): void
    {
        Event::fake();

        $host = User::factory()->create();
        $room = GameRoom::factory()->create([
            'game_id' => $this->game->id,
            'host_user_id' => $host->id,
            'status' => 'waiting',
            'visibility' => 'public',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/rooms/{$room->id}/join");

        $response->assertOk()
            ->assertJsonPath('message', 'Joined room successfully.');

        $this->assertDatabaseHas('game_room_members', [
            'game_room_id' => $room->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_cannot_join_a_room_in_progress(): void
    {
        $room = GameRoom::factory()->inGame()->create([
            'game_id' => $this->game->id,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/rooms/{$room->id}/join")
            ->assertForbidden();
    }

    public function test_user_cannot_join_same_room_twice(): void
    {
        Event::fake();

        $room = GameRoom::factory()->create([
            'game_id' => $this->game->id,
            'status' => 'waiting',
        ]);

        GameRoomMember::create([
            'game_room_id' => $room->id,
            'user_id' => $this->user->id,
            'role' => 'player',
            'joined_at' => now(),
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/rooms/{$room->id}/join")
            ->assertUnprocessable();
    }

    public function test_password_protected_room_requires_correct_password(): void
    {
        $room = GameRoom::factory()->withPassword('secret123')->create([
            'game_id' => $this->game->id,
            'status' => 'waiting',
        ]);

        // Wrong password.
        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/rooms/{$room->id}/join", ['password' => 'wrong'])
            ->assertUnprocessable();

        // Correct password.
        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/rooms/{$room->id}/join", ['password' => 'secret123'])
            ->assertOk();
    }

    // ── Leave Room ────────────────────────────────────────────────────────────

    public function test_user_can_leave_a_room(): void
    {
        Event::fake();

        $room = GameRoom::factory()->create([
            'game_id' => $this->game->id,
            'status' => 'waiting',
        ]);

        GameRoomMember::create([
            'game_room_id' => $room->id,
            'user_id' => $this->user->id,
            'role' => 'player',
            'joined_at' => now(),
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/rooms/{$room->id}/leave")
            ->assertOk();

        $this->assertDatabaseHas('game_room_members', [
            'game_room_id' => $room->id,
            'user_id' => $this->user->id,
        ]);

        $member = GameRoomMember::where('game_room_id', $room->id)
            ->where('user_id', $this->user->id)
            ->first();

        $this->assertNotNull($member->left_at);
    }

    public function test_user_cannot_leave_a_room_they_are_not_in(): void
    {
        $room = GameRoom::factory()->create([
            'game_id' => $this->game->id,
            'status' => 'waiting',
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/rooms/{$room->id}/leave")
            ->assertForbidden();
    }

    // ── Room Detail ───────────────────────────────────────────────────────────

    public function test_user_can_view_public_room_details(): void
    {
        $room = GameRoom::factory()->create([
            'game_id' => $this->game->id,
            'visibility' => 'public',
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/rooms/{$room->id}")
            ->assertOk()
            ->assertJsonPath('id', $room->id)
            ->assertJsonPath('code', $room->code);
    }

    public function test_private_room_is_not_visible_to_non_members(): void
    {
        $room = GameRoom::factory()->private()->create([
            'game_id' => $this->game->id,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/rooms/{$room->id}")
            ->assertForbidden();
    }
}

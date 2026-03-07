<?php

namespace App\Actions\Room;

use App\Events\Room\RoomCreated;
use App\Models\Game;
use App\Models\GameRoom;
use App\Models\GameRoomMember;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateRoomAction
{
    /**
     * Create a new game room and add the host as the first member.
     *
     * @param  array<string, mixed>  $data  Validated input
     */
    public function execute(User $host, Game $game, array $data): GameRoom
    {
        $room = GameRoom::create([
            'code' => $this->generateUniqueCode(),
            'name' => $data['name'] ?? null,
            'game_id' => $game->id,
            'host_user_id' => $host->id,
            'visibility' => $data['visibility'] ?? 'public',
            'status' => 'waiting',
            'password_hash' => isset($data['password']) ? bcrypt($data['password']) : null,
            'max_players' => $data['max_players'] ?? null,
            'allow_spectators' => $data['allow_spectators'] ?? false,
            'room_config' => $data['room_config'] ?? null,
        ]);

        GameRoomMember::create([
            'game_room_id' => $room->id,
            'user_id' => $host->id,
            'role' => 'host',
            'is_ready' => false,
            'joined_at' => now(),
        ]);

        event(new RoomCreated($room));

        return $room->load(['game', 'host', 'activeMembers']);
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (GameRoom::where('code', $code)->exists());

        return $code;
    }
}

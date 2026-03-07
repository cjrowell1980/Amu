<?php

namespace App\Actions\Room;

use App\Enums\RoomMemberRole;
use App\Enums\RoomStatus;
use App\Enums\RoomVisibility;
use App\Events\Room\RoomCreated;
use App\Models\Game;
use App\Models\GameRoom;
use App\Models\GameRoomMember;
use App\Models\User;
use App\Services\AuditService;
use App\Services\GameRegistryService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateRoomAction
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly GameRegistryService $registry,
    ) {}

    /**
     * Create a new game room and add the host as the first member.
     *
     * @param  array<string, mixed>  $data  Validated input
     */
    public function execute(User $host, Game $game, array $data): GameRoom
    {
        // Validate room config through the module if one is registered
        if ($this->registry->has($game->slug)) {
            $module = $this->registry->resolve($game->slug);
            $errors = $module->validateRoomConfig($data['room_config'] ?? []);
            if (! empty($errors)) {
                throw ValidationException::withMessages(['room_config' => $errors]);
            }
        }

        $room = GameRoom::create([
            'code'            => $this->generateUniqueCode(),
            'name'            => $data['name'] ?? null,
            'game_id'         => $game->id,
            'host_user_id'    => $host->id,
            'visibility'      => RoomVisibility::from($data['visibility'] ?? RoomVisibility::Public->value),
            'status'          => RoomStatus::Waiting,
            'password_hash'   => isset($data['password']) ? bcrypt($data['password']) : null,
            'max_players'     => $data['max_players'] ?? null,
            'allow_spectators'=> $data['allow_spectators'] ?? false,
            'room_config'     => $data['room_config'] ?? null,
        ]);

        GameRoomMember::create([
            'game_room_id' => $room->id,
            'user_id'      => $host->id,
            'role'         => RoomMemberRole::Host,
            'is_ready'     => true, // Host is always considered ready
            'joined_at'    => now(),
        ]);

        $this->audit->roomCreated($room);
        event(new RoomCreated($room));

        return $room->load(['game', 'host', 'activeMembers.user.profile']);
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (GameRoom::where('code', $code)->exists());

        return $code;
    }
}

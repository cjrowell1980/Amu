<?php

namespace App\Actions\Room;

use App\Enums\RoomMemberRole;
use App\Enums\RoomVisibility;
use App\Events\Room\PlayerJoinedRoom;
use App\Models\GameRoom;
use App\Models\GameRoomMember;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class JoinRoomAction
{
    public function __construct(private readonly AuditService $audit) {}

    public function execute(User $user, GameRoom $room, ?string $password = null, bool $asSpectator = false): GameRoomMember
    {
        $this->assertCanJoin($user, $room, $password);

        $role = $asSpectator && $room->allow_spectators
            ? RoomMemberRole::Spectator
            : RoomMemberRole::Player;

        // If user previously left, restore their membership.
        $existing = GameRoomMember::where('game_room_id', $room->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            $existing->update([
                'left_at'   => null,
                'is_ready'  => false,
                'role'      => $role,
                'joined_at' => now(),
            ]);
            $member = $existing->fresh();
        } else {
            $member = GameRoomMember::create([
                'game_room_id' => $room->id,
                'user_id'      => $user->id,
                'role'         => $role,
                'is_ready'     => false,
                'joined_at'    => now(),
            ]);
        }

        $this->audit->playerJoinedRoom($room, $user);
        event(new PlayerJoinedRoom($room, $user));

        return $member->load('user.profile');
    }

    private function assertCanJoin(User $user, GameRoom $room, ?string $password): void
    {
        if (! $room->status->acceptsNewMembers()) {
            throw ValidationException::withMessages([
                'room' => 'This room is no longer accepting players.',
            ]);
        }

        if ($room->isFull()) {
            throw ValidationException::withMessages([
                'room' => 'This room is full.',
            ]);
        }

        $alreadyActive = GameRoomMember::where('game_room_id', $room->id)
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->exists();

        if ($alreadyActive) {
            throw ValidationException::withMessages([
                'room' => 'You are already in this room.',
            ]);
        }

        if ($room->visibility === RoomVisibility::Private &&
            $room->hasPassword() &&
            ! Hash::check($password ?? '', $room->password_hash)) {
            throw ValidationException::withMessages([
                'password' => 'Incorrect room password.',
            ]);
        }
    }
}

<?php

namespace App\Policies;

use App\Enums\RoomVisibility;
use App\Models\GameRoom;
use App\Models\GameRoomMember;
use App\Models\User;

class GameRoomPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, GameRoom $room): bool
    {
        if ($room->visibility === RoomVisibility::Private) {
            return $user->id === $room->host_user_id
                || GameRoomMember::where('game_room_id', $room->id)
                    ->where('user_id', $user->id)
                    ->exists();
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create rooms');
    }

    public function update(User $user, GameRoom $room): bool
    {
        return $user->id === $room->host_user_id
            || $user->hasRole(['admin', 'operator', 'moderator']);
    }

    public function delete(User $user, GameRoom $room): bool
    {
        return $user->hasRole(['admin']);
    }

    public function join(User $user, GameRoom $room): bool
    {
        return $room->status->acceptsNewMembers() && ! $room->isFull();
    }

    public function leave(User $user, GameRoom $room): bool
    {
        return GameRoomMember::where('game_room_id', $room->id)
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->exists();
    }

    /**
     * Only the host can start a session, and only from waiting/ready status.
     */
    public function startSession(User $user, GameRoom $room): bool
    {
        return $user->id === $room->host_user_id
            && in_array($room->status->value, ['waiting', 'ready'], strict: true);
    }

    public function toggleReady(User $user, GameRoom $room): bool
    {
        return GameRoomMember::where('game_room_id', $room->id)
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->exists();
    }

    /**
     * Operators/admins/moderators can force-close a room.
     */
    public function forceClose(User $user, GameRoom $room): bool
    {
        return $user->hasRole(['admin', 'operator', 'moderator']);
    }
}

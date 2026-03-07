<?php

namespace App\Policies;

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
        if ($room->visibility === 'private') {
            return $user->id === $room->host_user_id
                || GameRoomMember::where('game_room_id', $room->id)
                    ->where('user_id', $user->id)
                    ->exists();
        }

        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, GameRoom $room): bool
    {
        return $user->id === $room->host_user_id || $user->hasRole(['admin', 'moderator']);
    }

    public function delete(User $user, GameRoom $room): bool
    {
        return $user->id === $room->host_user_id || $user->hasRole(['admin']);
    }

    public function join(User $user, GameRoom $room): bool
    {
        return $room->status === 'waiting' && ! $room->isFull();
    }

    public function leave(User $user, GameRoom $room): bool
    {
        return GameRoomMember::where('game_room_id', $room->id)
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->exists();
    }

    public function startSession(User $user, GameRoom $room): bool
    {
        return $user->id === $room->host_user_id;
    }

    public function toggleReady(User $user, GameRoom $room): bool
    {
        return GameRoomMember::where('game_room_id', $room->id)
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->exists();
    }
}

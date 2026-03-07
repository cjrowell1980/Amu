<?php

namespace App\Actions\Room;

use App\Events\Room\PlayerLeftRoom;
use App\Models\GameRoom;
use App\Models\GameRoomMember;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class LeaveRoomAction
{
    public function execute(User $user, GameRoom $room): void
    {
        $member = GameRoomMember::where('game_room_id', $room->id)
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->first();

        if (! $member) {
            throw ValidationException::withMessages([
                'room' => 'You are not in this room.',
            ]);
        }

        $member->update(['left_at' => now(), 'is_ready' => false]);

        event(new PlayerLeftRoom($room, $user));

        // If the host leaves and others remain, promote oldest remaining member.
        if ($room->host_user_id === $user->id) {
            $this->promoteNewHostOrClose($room, $user);
        }
    }

    private function promoteNewHostOrClose(GameRoom $room, User $departingHost): void
    {
        $nextMember = GameRoomMember::where('game_room_id', $room->id)
            ->whereNull('left_at')
            ->where('user_id', '!=', $departingHost->id)
            ->orderBy('joined_at')
            ->first();

        if ($nextMember) {
            $nextMember->update(['role' => 'host']);
            $room->update(['host_user_id' => $nextMember->user_id]);
        } else {
            $room->update(['status' => 'cancelled']);
        }
    }
}

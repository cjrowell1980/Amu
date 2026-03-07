<?php

namespace App\Actions\Room;

use App\Events\Room\PlayerLeftRoom;
use App\Models\GameRoom;
use App\Models\GameRoomMember;
use App\Models\User;
use App\Services\AuditService;
use App\Services\RoomLifecycleService;
use Illuminate\Validation\ValidationException;

class LeaveRoomAction
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly RoomLifecycleService $lifecycle,
    ) {}

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

        $this->audit->playerLeftRoom($room, $user);
        event(new PlayerLeftRoom($room, $user));

        $room->refresh();

        if ($member->isHost()) {
            // Delegate host-left logic to the lifecycle service
            $this->lifecycle->handleHostLeft($room, $user);
        } else {
            // Recalculate ready status in case this was the last unready player
            $this->lifecycle->recalculateReadyStatus($room);
        }
    }
}

<?php

namespace App\Events\Room;

use App\Models\GameRoom;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomReadyStateChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly GameRoom $room,
        public readonly int $userId,
        public readonly bool $isReady,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('room.' . $this->room->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'room.ready_state_changed';
    }

    public function broadcastWith(): array
    {
        $activeMembers = $this->room->activeMembers()->get();
        $readyCount = $activeMembers->where('is_ready', true)->count();

        return [
            'room_id' => $this->room->id,
            'user_id' => $this->userId,
            'is_ready' => $this->isReady,
            'ready_count' => $readyCount,
            'member_count' => $activeMembers->count(),
            'all_ready' => $readyCount === $activeMembers->count() && $activeMembers->count() > 0,
        ];
    }
}

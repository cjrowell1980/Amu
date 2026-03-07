<?php

namespace App\Events\Room;

use App\Models\GameRoom;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly GameRoom $room,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('platform.lobby'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'room.created';
    }

    public function broadcastWith(): array
    {
        return [
            'room_id' => $this->room->id,
            'code' => $this->room->code,
            'name' => $this->room->name,
            'game_slug' => $this->room->game?->slug,
            'status' => $this->room->status,
            'visibility' => $this->room->visibility,
        ];
    }
}

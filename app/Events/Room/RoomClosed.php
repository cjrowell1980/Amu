<?php

namespace App\Events\Room;

use App\Models\GameRoom;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomClosed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly GameRoom $room) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('platform.lobby'),
            new PresenceChannel("room.{$this->room->id}"),
        ];
    }

    public function broadcastAs(): string { return 'room.closed'; }

    public function broadcastWith(): array
    {
        return ['room_id' => $this->room->id];
    }
}

<?php

namespace App\Events\Room;

use App\Models\GameRoom;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HostTransferred implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly GameRoom $room,
        public readonly User $newHost,
    ) {}

    public function broadcastOn(): array
    {
        return [new PresenceChannel("room.{$this->room->id}")];
    }

    public function broadcastAs(): string { return 'room.host_transferred'; }

    public function broadcastWith(): array
    {
        return [
            'room_id'      => $this->room->id,
            'new_host_id'  => $this->newHost->id,
            'new_host_name'=> $this->newHost->profile?->display_name ?? $this->newHost->name,
        ];
    }
}

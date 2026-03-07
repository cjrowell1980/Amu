<?php

namespace App\Events\Session;

use App\Models\GameSession;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly GameSession $session,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('room.' . $this->session->game_room_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'session.created';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->session->id,
            'session_uuid' => $this->session->uuid,
            'game_id' => $this->session->game_id,
            'room_id' => $this->session->game_room_id,
            'status' => $this->session->status,
        ];
    }
}

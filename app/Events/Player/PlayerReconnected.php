<?php

namespace App\Events\Player;

use App\Models\GameSession;
use App\Models\GameSessionParticipant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerReconnected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly GameSession $session,
        public readonly GameSessionParticipant $participant,
    ) {}

    public function broadcastOn(): array
    {
        return [new PresenceChannel("session.{$this->session->uuid}")];
    }

    public function broadcastAs(): string { return 'player.reconnected'; }

    public function broadcastWith(): array
    {
        return [
            'session_uuid' => $this->session->uuid,
            'user_id'      => $this->participant->user_id,
            'reconnected_at' => now()->toIso8601String(),
        ];
    }
}

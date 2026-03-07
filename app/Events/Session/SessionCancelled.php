<?php

namespace App\Events\Session;

use App\Models\GameSession;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionCancelled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly GameSession $session,
        public readonly string $reason = 'manual',
    ) {}

    public function broadcastOn(): array
    {
        return [new PresenceChannel("session.{$this->session->uuid}")];
    }

    public function broadcastAs(): string { return 'session.cancelled'; }

    public function broadcastWith(): array
    {
        return [
            'session_uuid' => $this->session->uuid,
            'reason'       => $this->reason,
        ];
    }
}

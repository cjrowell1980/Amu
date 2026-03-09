<?php

namespace Amu\Core\Services;

use Amu\Core\Contracts\SessionManager;
use Amu\Core\Enums\RoomStatus;
use Amu\Core\Enums\SessionStatus;
use Amu\Core\Events\SessionEnded;
use Amu\Core\Events\SessionStarted;
use Amu\Core\Models\GameRoom;
use Amu\Core\Models\GameSession;
use RuntimeException;

class EloquentSessionManager implements SessionManager
{
    public function create(GameRoom $room, array $attributes = []): GameSession
    {
        return GameSession::query()->create([
            'game_module_id' => $room->game_module_id,
            'game_room_id' => $room->id,
            'status' => $attributes['status'] ?? SessionStatus::Pending,
            'settings' => $attributes['settings'] ?? [],
        ])->load('module');
    }

    public function transition(GameSession $session, SessionStatus $status): GameSession
    {
        if (! $session->status->canTransitionTo($status)) {
            throw new RuntimeException("Cannot transition session from {$session->status->value} to {$status->value}.");
        }

        $session->forceFill([
            'status' => $status,
            'started_at' => $status === SessionStatus::Active && $session->started_at === null ? now() : $session->started_at,
            'ended_at' => in_array($status, [SessionStatus::Finished, SessionStatus::Cancelled], true) ? now() : $session->ended_at,
        ])->save();

        if ($status === SessionStatus::Active) {
            $session->room->forceFill(['status' => RoomStatus::Active])->save();
            event(new SessionStarted($session));
        }

        if (in_array($status, [SessionStatus::Finished, SessionStatus::Cancelled], true)) {
            $session->room->forceFill(['status' => RoomStatus::Closed])->save();
            event(new SessionEnded($session));
        }

        return $session->refresh()->load('module');
    }
}

<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Record a platform event in the audit log.
     *
     * @param  string      $event    Machine-readable event name (e.g. 'room.created')
     * @param  Model|null  $subject  The primary entity affected
     * @param  array       $metadata Extra structured context
     * @param  User|null   $actor    The user who triggered the event (defaults to authenticated user)
     */
    public function log(
        string $event,
        ?Model $subject = null,
        array $metadata = [],
        ?User $actor = null,
    ): AuditLog {
        $actor ??= Auth::user();

        return AuditLog::create([
            'user_id'      => $actor?->id,
            'event'        => $event,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject?->getKey(),
            'metadata'     => $metadata ?: null,
            'ip_address'   => Request::ip(),
            'user_agent'   => Request::userAgent(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Named helpers for common platform events
    // -------------------------------------------------------------------------

    public function userRegistered(User $user): void
    {
        $this->log('user.registered', $user, [
            'email' => $user->email,
        ], $user);
    }

    public function userLoggedIn(User $user): void
    {
        $this->log('user.logged_in', $user, [], $user);
    }

    public function roomCreated(\App\Models\GameRoom $room): void
    {
        $this->log('room.created', $room, [
            'game_id'    => $room->game_id,
            'visibility' => $room->visibility->value,
        ]);
    }

    public function roomClosed(\App\Models\GameRoom $room, string $reason = 'manual'): void
    {
        $this->log('room.closed', $room, ['reason' => $reason]);
    }

    public function roomCancelled(\App\Models\GameRoom $room, string $reason = 'manual'): void
    {
        $this->log('room.cancelled', $room, ['reason' => $reason]);
    }

    public function playerJoinedRoom(\App\Models\GameRoom $room, User $player): void
    {
        $this->log('room.player_joined', $room, ['player_id' => $player->id], $player);
    }

    public function playerLeftRoom(\App\Models\GameRoom $room, User $player): void
    {
        $this->log('room.player_left', $room, ['player_id' => $player->id], $player);
    }

    public function readyStateChanged(\App\Models\GameRoom $room, User $player, bool $isReady): void
    {
        $this->log('room.ready_state_changed', $room, [
            'player_id' => $player->id,
            'is_ready'  => $isReady,
        ], $player);
    }

    public function sessionCreated(\App\Models\GameSession $session): void
    {
        $this->log('session.created', $session, [
            'room_id' => $session->game_room_id,
            'game_id' => $session->game_id,
        ]);
    }

    public function sessionStarted(\App\Models\GameSession $session): void
    {
        $this->log('session.started', $session, [
            'room_id'    => $session->game_room_id,
            'game_id'    => $session->game_id,
            'started_at' => now()->toIso8601String(),
        ]);
    }

    public function sessionCompleted(\App\Models\GameSession $session): void
    {
        $this->log('session.completed', $session, [
            'ended_at' => now()->toIso8601String(),
        ]);
    }

    public function sessionCancelled(\App\Models\GameSession $session, string $reason = 'manual'): void
    {
        $this->log('session.cancelled', $session, ['reason' => $reason]);
    }

    public function gameAvailabilityChanged(\App\Models\Game $game, string $from, string $to): void
    {
        $this->log('game.availability_changed', $game, [
            'from' => $from,
            'to'   => $to,
        ]);
    }

    public function playerDisconnected(\App\Models\GameSessionParticipant $participant): void
    {
        $this->log('player.disconnected', $participant->session, [
            'player_id' => $participant->user_id,
        ], $participant->user);
    }

    public function playerReconnected(\App\Models\GameSessionParticipant $participant): void
    {
        $this->log('player.reconnected', $participant->session, [
            'player_id' => $participant->user_id,
        ], $participant->user);
    }
}

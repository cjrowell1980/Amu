<?php

use App\Models\GameRoom;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Platform real-time channel definitions.
|
| Public channels    — no auth required; all clients receive events.
| Presence channels  — authenticated; server tracks who is listening.
| Private channels   — authenticated; per-user private data.
|
| Channel names must match those used in event broadcastOn() methods.
|
*/

// ── Platform-wide private user channel ───────────────────────────────────────
Broadcast::channel('App.Models.User.{id}', function (User $user, int $id) {
    return (int) $user->id === $id;
});

// ── Platform lobby (public) ───────────────────────────────────────────────────
// Receives: room.created, room.updated, room.status_changed, room.closed, room.cancelled
// No authorization — all authenticated users can subscribe.
Broadcast::channel('platform.lobby', function (User $user) {
    return ['id' => $user->id, 'name' => $user->name];
});

// ── Room presence channel ─────────────────────────────────────────────────────
// Receives: player.joined, player.left, room.ready_state_changed, room.status_changed,
//           room.host_transferred, room.closed, session.created
// Authorized for: active room members + any operator/admin
Broadcast::channel('room.{roomId}', function (User $user, int $roomId) {
    $room = GameRoom::find($roomId);

    if (! $room) {
        return false;
    }

    if ($user->hasRole(['admin', 'operator', 'moderator'])) {
        return ['id' => $user->id, 'name' => $user->name, 'role' => 'operator'];
    }

    $member = $room->members()
        ->where('user_id', $user->id)
        ->whereNull('left_at')
        ->first();

    if (! $member) {
        return false;
    }

    return [
        'id'   => $user->id,
        'name' => $user->profile?->display_name ?? $user->name,
        'role' => $member->role->value,
    ];
});

// ── Session presence channel ──────────────────────────────────────────────────
// Receives: session.status_changed, session.started, session.completed,
//           session.cancelled, player.disconnected, player.reconnected
// Authorized for: session participants + operators/admins
Broadcast::channel('session.{uuid}', function (User $user, string $uuid) {
    $session = GameSession::where('uuid', $uuid)->first();

    if (! $session) {
        return false;
    }

    if ($user->hasRole(['admin', 'operator', 'moderator'])) {
        return ['id' => $user->id, 'name' => $user->name, 'role' => 'operator'];
    }

    $participant = $session->participants()
        ->where('user_id', $user->id)
        ->first();

    if (! $participant) {
        return false;
    }

    return [
        'id'   => $user->id,
        'name' => $user->profile?->display_name ?? $user->name,
        'role' => $participant->role->value,
    ];
});

// ── Private per-user game state channel ───────────────────────────────────────
// Used to push private state (hidden cards, etc.) to a specific player.
// Receives: private.state events sent from game modules
Broadcast::channel('private-player.{userId}.session.{uuid}', function (User $user, int $userId, string $uuid) {
    if ((int) $user->id !== $userId) {
        return false;
    }

    $session = GameSession::where('uuid', $uuid)->first();

    if (! $session) {
        return false;
    }

    return $session->participants()->where('user_id', $user->id)->exists();
});

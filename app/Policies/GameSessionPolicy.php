<?php

namespace App\Policies;

use App\Models\GameSession;
use App\Models\User;

class GameSessionPolicy
{
    public function view(User $user, GameSession $session): bool
    {
        // Participants, room host, and operators can view
        if ($user->hasRole(['admin', 'operator', 'moderator'])) {
            return true;
        }

        if ($user->id === $session->room->host_user_id) {
            return true;
        }

        return $session->participants()->where('user_id', $user->id)->exists();
    }

    /**
     * Only the room host can start a session.
     */
    public function start(User $user, GameSession $session): bool
    {
        return $user->id === $session->room->host_user_id;
    }

    /**
     * Only active participants can submit actions.
     */
    public function act(User $user, GameSession $session): bool
    {
        return $session->participants()
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->exists();
    }

    /**
     * Operators/admins can force-end sessions.
     */
    public function forceEnd(User $user, GameSession $session): bool
    {
        return $user->hasRole(['admin', 'operator', 'moderator']);
    }
}

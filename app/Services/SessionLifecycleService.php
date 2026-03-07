<?php

namespace App\Services;

use App\Enums\ConnectionStatus;
use App\Enums\RoomStatus;
use App\Enums\SessionStatus;
use App\Events\Player\PlayerDisconnected;
use App\Events\Player\PlayerReconnected;
use App\Events\Session\SessionCancelled;
use App\Events\Session\SessionCompleted;
use App\Events\Session\SessionStarted;
use App\Events\Session\SessionStatusChanged;
use App\Models\GameSession;
use App\Models\GameSessionParticipant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SessionLifecycleService
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly GameRegistryService $registry,
    ) {}

    // -------------------------------------------------------------------------
    // State transitions
    // -------------------------------------------------------------------------

    /**
     * Transition session to a new status, enforcing valid transition rules.
     *
     * @throws RuntimeException if the transition is not allowed.
     */
    public function transitionTo(GameSession $session, SessionStatus $next): GameSession
    {
        if (! $session->status->canTransitionTo($next)) {
            throw new RuntimeException(
                "Cannot transition session [{$session->uuid}] from [{$session->status->value}] to [{$next->value}]."
            );
        }

        $session->status = $next;
        $session->save();

        broadcast(new SessionStatusChanged($session))->toOthers();

        return $session;
    }

    // -------------------------------------------------------------------------
    // Start session
    // -------------------------------------------------------------------------

    /**
     * Start a session: transition room to in_progress, session to active,
     * invoke the game module's onSessionStart hook.
     */
    public function startSession(GameSession $session): GameSession
    {
        return DB::transaction(function () use ($session) {
            if (! $session->canTransitionTo(SessionStatus::Starting)) {
                throw new RuntimeException("Session [{$session->uuid}] cannot be started from status [{$session->status->value}].");
            }

            $this->transitionTo($session, SessionStatus::Starting);

            // Move room to in_progress
            $room = $session->room;
            $room->status = RoomStatus::InProgress;
            $room->save();

            $session->started_at = now();
            $this->transitionTo($session, SessionStatus::Active);
            $session->save();

            // Call game module hook
            try {
                $module = $this->registry->resolve($session->game->slug);
                $module->onSessionStart($session);
            } catch (\Throwable $e) {
                // Module hook failure should not block session start; log and continue
                logger()->error("Module onSessionStart failed for session [{$session->uuid}]: {$e->getMessage()}");
            }

            // Mark all participants connected
            $session->participants()->update([
                'connection_status' => ConnectionStatus::Connected->value,
                'last_seen_at'      => now(),
            ]);

            $this->audit->sessionStarted($session);
            broadcast(new SessionStarted($session))->toOthers();

            return $session->fresh();
        });
    }

    // -------------------------------------------------------------------------
    // Complete session
    // -------------------------------------------------------------------------

    /**
     * Mark session as completed. Calls module's onSessionEnd and persistResults hooks.
     */
    public function completeSession(GameSession $session, array $resultSummary = []): GameSession
    {
        return DB::transaction(function () use ($session, $resultSummary) {
            $this->transitionTo($session, SessionStatus::Completed);

            $session->ended_at      = now();
            $session->result_summary = $resultSummary ?: $session->result_summary;
            $session->save();

            // Call module hooks
            try {
                $module = $this->registry->resolve($session->game->slug);
                $computedResults = $module->onSessionEnd($session);
                $module->persistResults($session, $computedResults);

                // Increment game play count
                $session->game->incrementPlayCount();
            } catch (\Throwable $e) {
                logger()->error("Module onSessionEnd/persistResults failed for session [{$session->uuid}]: {$e->getMessage()}");
            }

            // Close room
            $room = $session->room;
            if (! $room->status->isTerminal()) {
                $room->status = RoomStatus::Completed;
                $room->save();
            }

            $this->audit->sessionCompleted($session);
            broadcast(new SessionCompleted($session))->toOthers();

            return $session->fresh();
        });
    }

    // -------------------------------------------------------------------------
    // Cancel session
    // -------------------------------------------------------------------------

    /**
     * Cancel a session (operator or system action).
     */
    public function cancelSession(GameSession $session, string $reason = 'manual'): GameSession
    {
        return DB::transaction(function () use ($session, $reason) {
            $this->transitionTo($session, SessionStatus::Cancelled);
            $session->ended_at = now();
            $session->save();

            // Mark room as cancelled if still active
            $room = $session->room;
            if (! $room->status->isTerminal()) {
                $room->status = RoomStatus::Cancelled;
                $room->save();
            }

            $this->audit->sessionCancelled($session, $reason);
            broadcast(new SessionCancelled($session, $reason))->toOthers();

            return $session->fresh();
        });
    }

    // -------------------------------------------------------------------------
    // Pause / resume
    // -------------------------------------------------------------------------

    public function pauseSession(GameSession $session): GameSession
    {
        return $this->transitionTo($session, SessionStatus::Paused);
    }

    public function resumeSession(GameSession $session): GameSession
    {
        return $this->transitionTo($session, SessionStatus::Active);
    }

    // -------------------------------------------------------------------------
    // Reconnect / disconnect
    // -------------------------------------------------------------------------

    /**
     * Mark a participant as disconnected and broadcast the event.
     */
    public function markDisconnected(GameSessionParticipant $participant): void
    {
        $participant->update([
            'connection_status' => ConnectionStatus::Disconnected->value,
            'disconnected_at'   => now(),
            'reconnect_token'   => $this->generateReconnectToken(),
        ]);

        $this->audit->playerDisconnected($participant);
        broadcast(new PlayerDisconnected($participant->session, $participant))->toOthers();
    }

    /**
     * Mark a participant as reconnected (clears disconnect fields).
     */
    public function markReconnected(GameSessionParticipant $participant): void
    {
        $participant->update([
            'connection_status' => ConnectionStatus::Connected->value,
            'disconnected_at'   => null,
            'reconnect_token'   => null,
            'last_seen_at'      => now(),
        ]);

        $this->audit->playerReconnected($participant);
        broadcast(new PlayerReconnected($participant->session, $participant))->toOthers();
    }

    /**
     * Update last_seen_at for a participant (heartbeat).
     */
    public function heartbeat(GameSessionParticipant $participant): void
    {
        $participant->update(['last_seen_at' => now()]);
    }

    /**
     * Find a participant by reconnect token for re-authentication.
     */
    public function findByReconnectToken(string $token): ?GameSessionParticipant
    {
        return GameSessionParticipant::where('reconnect_token', $token)->first();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function generateReconnectToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}

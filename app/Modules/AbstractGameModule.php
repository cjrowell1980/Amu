<?php

namespace App\Modules;

use App\Contracts\GameModuleInterface;
use App\Models\GameRoom;
use App\Models\GameSession;
use App\Models\GameSessionParticipant;

/**
 * Optional abstract base class for game modules.
 *
 * Provides sensible no-op defaults so module authors can override
 * only what their game needs. Extend this instead of implementing
 * GameModuleInterface directly unless you need fine-grained control.
 *
 * @see GameModuleInterface for full contract documentation.
 */
abstract class AbstractGameModule implements GameModuleInterface
{
    /**
     * {@inheritdoc}
     *
     * Default: no validation errors. Override to add rules.
     */
    public function validateRoomConfig(array $config): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * Default: return resolved config as-is. Override to add game state.
     */
    public function buildSessionConfig(GameRoom $room, array $resolvedConfig): array
    {
        return $resolvedConfig;
    }

    /**
     * {@inheritdoc}
     *
     * Default: always allow start.
     */
    public function onSessionStart(GameSession $session): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * Default: return empty state. Override to expose meaningful game state.
     */
    public function getPublicState(GameSession $session): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * Default: return empty private state. Override to expose hidden info.
     */
    public function getPrivateState(GameSession $session, GameSessionParticipant $participant): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * Default: return empty result summary. Override to compute winners.
     */
    public function onSessionEnd(GameSession $session): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * Default: no-op. Override to persist game-specific post-game data.
     */
    public function persistResults(GameSession $session, array $resultSummary): void
    {
        // no-op by default
    }

    // -------------------------------------------------------------------------
    // Helpers available to all modules
    // -------------------------------------------------------------------------

    /**
     * Read the session config (the persisted state blob).
     */
    protected function getConfig(GameSession $session): array
    {
        return $session->session_config ?? [];
    }

    /**
     * Merge new state into the session config and persist it.
     */
    protected function updateConfig(GameSession $session, array $newState): void
    {
        $session->session_config = array_merge($this->getConfig($session), $newState);
        $session->save();
    }

    /**
     * Replace the entire session config and persist it.
     */
    protected function setConfig(GameSession $session, array $state): void
    {
        $session->session_config = $state;
        $session->save();
    }
}

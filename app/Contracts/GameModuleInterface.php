<?php

namespace App\Contracts;

use App\Models\GameRoom;
use App\Models\GameSession;
use App\Models\User;

/**
 * Contract that every game module MUST implement.
 *
 * The platform core calls these methods at appropriate lifecycle points.
 * Game modules MUST NOT reach outside their own logic — all persistence
 * is handled by the platform before/after calling these methods.
 */
interface GameModuleInterface
{
    /**
     * Human-readable name of this game.
     */
    public function getName(): string;

    /**
     * Unique machine-readable slug matching the games.slug column.
     */
    public function getSlug(): string;

    /**
     * Return the version string for compatibility tracking.
     */
    public function getVersion(): string;

    /**
     * Validate the proposed room configuration before a room is created or updated.
     *
     * @param  array<string, mixed>  $config  Proposed room_config values
     * @return array<string, string>  Keyed validation errors; empty = valid
     */
    public function validateRoomConfig(array $config): array;

    /**
     * Build the initial session state when the platform creates a new GameSession.
     *
     * @param  GameRoom  $room  The room that is starting
     * @param  array<string, mixed>  $resolvedConfig  Merged game + room config
     * @return array<string, mixed>  Initial session_config to persist
     */
    public function buildSessionConfig(GameRoom $room, array $resolvedConfig): array;

    /**
     * Called when the platform transitions a session from 'created' → 'active'.
     * May perform any game-specific setup; return false to abort start.
     */
    public function onSessionStart(GameSession $session): bool;

    /**
     * Process a player action submitted via the platform action handler.
     *
     * @param  GameSession  $session
     * @param  User  $actor
     * @param  string  $actionType  Game-defined action identifier
     * @param  array<string, mixed>  $payload  Action-specific data
     * @return array<string, mixed>  Result/response data; game-defined shape
     */
    public function handlePlayerAction(
        GameSession $session,
        User $actor,
        string $actionType,
        array $payload
    ): array;

    /**
     * Return the public game state visible to all participants.
     *
     * @return array<string, mixed>
     */
    public function getPublicState(GameSession $session): array;

    /**
     * Return the private game state visible only to the given user.
     *
     * @return array<string, mixed>
     */
    public function getPrivateState(GameSession $session, User $user): array;

    /**
     * Called when the platform ends a session.
     * Must return a result summary to be persisted in game_sessions.result_summary.
     *
     * @return array<string, mixed>
     */
    public function onSessionEnd(GameSession $session): array;

    /**
     * Persist any game-specific post-game data (e.g. per-player stats).
     * Called after onSessionEnd. The result summary from onSessionEnd is provided.
     *
     * @param  array<string, mixed>  $resultSummary
     */
    public function persistResults(GameSession $session, array $resultSummary): void;
}

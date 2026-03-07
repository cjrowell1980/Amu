<?php

namespace App\Contracts;

use App\Models\GameRoom;
use App\Models\GameSession;
use App\Models\GameSessionParticipant;

/**
 * Contract that every game module MUST implement.
 *
 * ┌────────────────────────────────────────────────────────────────────────┐
 * │  PLATFORM LIFECYCLE — when each method is called                       │
 * ├────────────────────────────────────────────────────────────────────────┤
 * │  Room creation   → validateRoomConfig()                                │
 * │  Session created → buildSessionConfig()                                │
 * │  Session starts  → onSessionStart()                                    │
 * │  Player action   → handlePlayerAction()                                │
 * │  State query     → getPublicState() / getPrivateState()                │
 * │  Session ends    → onSessionEnd() → persistResults()                   │
 * └────────────────────────────────────────────────────────────────────────┘
 *
 * Design rules for module authors:
 *  - Modules MUST NOT reach outside their own logic for platform concerns.
 *  - All platform persistence (participants, scores) is handled by the
 *    platform core; use persistResults() only for game-specific extras.
 *  - Modules should be stateless; all state must go through session_config.
 *  - If your method fails, throw an exception; the platform will handle it.
 */
interface GameModuleInterface
{
    /**
     * Human-readable name of this game.
     * Displayed in the lobby and admin UI.
     */
    public function getName(): string;

    /**
     * Unique machine-readable slug matching the games.slug column.
     * Must be lowercase, hyphen-separated (e.g. 'blackjack', 'texas-holdem').
     */
    public function getSlug(): string;

    /**
     * Semantic version string for compatibility tracking (e.g. '1.0.0').
     * Increment when session_config schema changes in a breaking way.
     */
    public function getVersion(): string;

    /**
     * Validate the proposed room configuration before a room is created.
     *
     * Return an array of validation errors keyed by field name.
     * Return an empty array if the config is valid.
     *
     * @param  array<string, mixed>  $config  Proposed room_config values
     * @return array<string, string>  Keyed validation errors; empty = valid
     */
    public function validateRoomConfig(array $config): array;

    /**
     * Build the initial session state when the platform creates a new GameSession.
     *
     * Receives the merged game defaults + room-level overrides.
     * Should return the full session_config array to be persisted.
     *
     * @param  GameRoom              $room           The room that is starting
     * @param  array<string, mixed>  $resolvedConfig Merged game + room config
     * @return array<string, mixed>  Initial session_config to persist
     */
    public function buildSessionConfig(GameRoom $room, array $resolvedConfig): array;

    /**
     * Called when the platform transitions a session to 'active'.
     * Perform any game-specific initialisation here (e.g. shuffle deck, assign seats).
     * Return true to confirm start; return false to prevent starting (rare).
     */
    public function onSessionStart(GameSession $session): bool;

    /**
     * Process a player action submitted to the session.
     *
     * The platform validates the participant is active before calling this.
     * Return a response payload that will be sent back to the player.
     *
     * @param  GameSession              $session     The active session
     * @param  GameSessionParticipant   $participant The acting participant
     * @param  array<string, mixed>     $payload     Action data (type, ...params)
     * @return array<string, mixed>                  Result/response (game-defined shape)
     */
    public function handlePlayerAction(
        GameSession $session,
        GameSessionParticipant $participant,
        array $payload,
    ): array;

    /**
     * Return the public game state visible to ALL participants (and spectators).
     *
     * This is what gets broadcast on the public session channel.
     * Do NOT include private information such as hidden cards.
     *
     * @return array<string, mixed>
     */
    public function getPublicState(GameSession $session): array;

    /**
     * Return the private game state visible only to the given participant.
     *
     * May include hidden hand cards, private tokens, etc.
     * The platform sends this only over a private authenticated channel.
     *
     * @return array<string, mixed>
     */
    public function getPrivateState(GameSession $session, GameSessionParticipant $participant): array;

    /**
     * Called when the platform ends a session (completed or abandoned).
     *
     * Compute and return the result summary that will be stored in
     * game_sessions.result_summary. This is the canonical session result.
     *
     * @return array<string, mixed>  Result summary (game-defined shape)
     */
    public function onSessionEnd(GameSession $session): array;

    /**
     * Persist any game-specific post-game data (e.g. per-player ELO, stats).
     *
     * Called immediately after onSessionEnd(). The platform handles
     * updating GameSessionParticipant.score / final_rank separately;
     * use this only for data outside the core platform tables.
     *
     * @param  array<string, mixed>  $resultSummary  Return value of onSessionEnd()
     */
    public function persistResults(GameSession $session, array $resultSummary): void;
}

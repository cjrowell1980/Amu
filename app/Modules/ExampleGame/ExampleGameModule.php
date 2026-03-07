<?php

namespace App\Modules\ExampleGame;

use App\Contracts\GameModuleInterface;
use App\Models\GameRoom;
use App\Models\GameSession;
use App\Models\User;

/**
 * ExampleGameModule — a minimal stub that demonstrates the module contract.
 *
 * This is NOT a real game. It exists only to:
 *   1. Prove the GameModuleInterface can be satisfied.
 *   2. Show developers what a module implementation looks like.
 *   3. Be registered in the game registry as a reference entry.
 *
 * Delete or replace this when a real game module is added.
 */
class ExampleGameModule implements GameModuleInterface
{
    public function getName(): string
    {
        return 'Example Game';
    }

    public function getSlug(): string
    {
        return 'example-game';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function validateRoomConfig(array $config): array
    {
        $errors = [];

        if (isset($config['rounds']) && ((int) $config['rounds'] < 1 || (int) $config['rounds'] > 20)) {
            $errors['rounds'] = 'Rounds must be between 1 and 20.';
        }

        return $errors;
    }

    public function buildSessionConfig(GameRoom $room, array $resolvedConfig): array
    {
        return array_merge([
            'rounds' => 3,
            'turn_timeout_seconds' => 30,
        ], $resolvedConfig);
    }

    public function onSessionStart(GameSession $session): bool
    {
        // Nothing to do for the stub. Return true to allow start.
        return true;
    }

    public function handlePlayerAction(
        GameSession $session,
        User $actor,
        string $actionType,
        array $payload
    ): array {
        return [
            'acknowledged' => true,
            'action' => $actionType,
            'message' => 'ExampleGame: action received but not processed.',
        ];
    }

    public function getPublicState(GameSession $session): array
    {
        return [
            'session_id' => $session->id,
            'status' => $session->status,
            'message' => 'No public state — this is a stub module.',
        ];
    }

    public function getPrivateState(GameSession $session, User $user): array
    {
        return [
            'session_id' => $session->id,
            'user_id' => $user->id,
            'message' => 'No private state — this is a stub module.',
        ];
    }

    public function onSessionEnd(GameSession $session): array
    {
        return [
            'winner' => null,
            'scores' => [],
            'message' => 'ExampleGame ended — stub result.',
        ];
    }

    public function persistResults(GameSession $session, array $resultSummary): void
    {
        // No game-specific persistence needed for the stub.
    }
}

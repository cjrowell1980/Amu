<?php

namespace App\Modules\ExampleGame;

use App\Models\GameRoom;
use App\Models\GameSession;
use App\Models\GameSessionParticipant;
use App\Modules\AbstractGameModule;

/**
 * ExampleGameModule — a minimal stub demonstrating the module contract.
 *
 * ┌─────────────────────────────────────────────────────────────────────────┐
 * │  THIS IS NOT A REAL GAME.                                               │
 * │                                                                         │
 * │  It exists to:                                                          │
 * │    1. Prove the AbstractGameModule / GameModuleInterface can be          │
 * │       satisfied without error.                                          │
 * │    2. Show developers a reference implementation to copy from.           │
 * │    3. Exercise the platform core (registry, session lifecycle, etc.)    │
 * │       in tests.                                                         │
 * │                                                                         │
 * │  When you build a real game, copy this folder, rename it, update       │
 * │  getSlug() / getName(), and override the methods you need.             │
 * └─────────────────────────────────────────────────────────────────────────┘
 */
class ExampleGameModule extends AbstractGameModule
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

    /**
     * Optional config validation — validates 'rounds' if provided.
     */
    public function validateRoomConfig(array $config): array
    {
        $errors = [];

        if (isset($config['rounds'])) {
            $rounds = (int) $config['rounds'];
            if ($rounds < 1 || $rounds > 20) {
                $errors['rounds'] = 'Rounds must be between 1 and 20.';
            }
        }

        return $errors;
    }

    /**
     * Set default session config values.
     */
    public function buildSessionConfig(GameRoom $room, array $resolvedConfig): array
    {
        return array_merge([
            'rounds'               => 3,
            'turn_timeout_seconds' => 30,
            'current_round'        => 0,
            'scores'               => [],
        ], $resolvedConfig);
    }

    /**
     * Initialise scores for all participants on session start.
     */
    public function onSessionStart(GameSession $session): bool
    {
        $participants = $session->participants()->with('user')->get();

        $scores = [];
        foreach ($participants as $participant) {
            $scores[$participant->user_id] = 0;
        }

        $this->updateConfig($session, ['scores' => $scores, 'current_round' => 1]);

        return true;
    }

    /**
     * Handle a generic 'increment_score' action for demo purposes.
     */
    public function handlePlayerAction(
        GameSession $session,
        GameSessionParticipant $participant,
        array $payload,
    ): array {
        $action = $payload['type'] ?? 'unknown';

        if ($action === 'increment_score') {
            $config = $this->getConfig($session);
            $scores = $config['scores'] ?? [];
            $scores[$participant->user_id] = ($scores[$participant->user_id] ?? 0) + 1;
            $this->updateConfig($session, ['scores' => $scores]);

            return ['acknowledged' => true, 'new_score' => $scores[$participant->user_id]];
        }

        return [
            'acknowledged' => true,
            'action'       => $action,
            'message'      => 'ExampleGame: action received but not processed.',
        ];
    }

    public function getPublicState(GameSession $session): array
    {
        $config = $this->getConfig($session);

        return [
            'current_round' => $config['current_round'] ?? 1,
            'total_rounds'  => $config['rounds'] ?? 3,
            'scores'        => $config['scores'] ?? [],
        ];
    }

    public function getPrivateState(GameSession $session, GameSessionParticipant $participant): array
    {
        $config = $this->getConfig($session);

        return [
            'my_score' => $config['scores'][$participant->user_id] ?? 0,
        ];
    }

    /**
     * Determine final scores and return result summary.
     */
    public function onSessionEnd(GameSession $session): array
    {
        $config = $this->getConfig($session);
        $scores = $config['scores'] ?? [];

        arsort($scores);

        $ranked = [];
        $rank   = 1;
        foreach ($scores as $userId => $score) {
            $ranked[] = [
                'user_id' => (int) $userId,
                'score'   => $score,
                'rank'    => $rank++,
            ];
        }

        return [
            'winner'   => $ranked[0] ?? null,
            'rankings' => $ranked,
        ];
    }

    /**
     * Persist final ranks back to participant records.
     */
    public function persistResults(GameSession $session, array $resultSummary): void
    {
        foreach ($resultSummary['rankings'] ?? [] as $entry) {
            $session->participants()
                ->where('user_id', $entry['user_id'])
                ->update([
                    'final_rank' => $entry['rank'],
                    'score'      => $entry['score'],
                ]);
        }
    }
}

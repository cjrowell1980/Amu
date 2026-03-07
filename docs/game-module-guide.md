# Game Module Guide

This guide explains how to add a new game to the Amu platform.

## Overview

Each game is a PHP class that implements `App\Contracts\GameModuleInterface` (or extends `App\Modules\AbstractGameModule`). The platform handles all lifecycle orchestration; the module provides game logic only.

## Step 1: Create the module class

Create a folder `app/Modules/YourGame/` and add your module class:

```php
<?php

namespace App\Modules\YourGame;

use App\Models\GameRoom;
use App\Models\GameSession;
use App\Models\GameSessionParticipant;
use App\Modules\AbstractGameModule;

class YourGameModule extends AbstractGameModule
{
    public function getName(): string { return 'Your Game'; }
    public function getSlug(): string { return 'your-game'; }
    public function getVersion(): string { return '1.0.0'; }

    public function validateRoomConfig(array $config): array
    {
        $errors = [];
        // validate game-specific room options
        return $errors;
    }

    public function buildSessionConfig(GameRoom $room, array $resolvedConfig): array
    {
        return array_merge([
            'initial_state' => 'my default',
        ], $resolvedConfig);
    }

    public function onSessionStart(GameSession $session): bool
    {
        // Initialise game state — e.g. shuffle deck, assign hands
        $this->setConfig($session, [
            'deck'          => $this->buildShuffledDeck(),
            'current_round' => 1,
        ]);
        return true;
    }

    public function handlePlayerAction(
        GameSession $session,
        GameSessionParticipant $participant,
        array $payload,
    ): array {
        $action = $payload['type'] ?? null;

        // Process action, update state
        $config = $this->getConfig($session);
        // ... mutate $config ...
        $this->setConfig($session, $config);

        return ['result' => 'ok'];
    }

    public function getPublicState(GameSession $session): array
    {
        $config = $this->getConfig($session);
        return ['current_round' => $config['current_round'] ?? 1];
    }

    public function getPrivateState(GameSession $session, GameSessionParticipant $participant): array
    {
        // Return player-specific hidden state (e.g. their hand)
        $config = $this->getConfig($session);
        return ['my_hand' => $config['hands'][$participant->user_id] ?? []];
    }

    public function onSessionEnd(GameSession $session): array
    {
        // Compute and return result summary
        return ['winner' => null, 'rankings' => []];
    }

    public function persistResults(GameSession $session, array $resultSummary): void
    {
        foreach ($resultSummary['rankings'] as $entry) {
            $session->participants()
                ->where('user_id', $entry['user_id'])
                ->update(['final_rank' => $entry['rank'], 'score' => $entry['score']]);
        }
    }

    private function buildShuffledDeck(): array
    {
        // ... your deck logic ...
        return [];
    }
}
```

## Step 2: Register the module

In `app/Providers/AppServiceProvider.php`, add to `registerGameModules()`:

```php
$registry->register(new \App\Modules\YourGame\YourGameModule());
```

## Step 3: Add the database record

In `database/seeders/GameSeeder.php` (or via a migration):

```php
Game::updateOrCreate(['slug' => 'your-game'], [
    'name'           => 'Your Game',
    'description'    => 'Game description.',
    'module_class'   => \App\Modules\YourGame\YourGameModule::class,
    'availability'   => GameAvailability::Hidden->value,  // start hidden
    'min_players'    => 2,
    'max_players'    => 8,
    'default_config' => ['my_option' => true],
]);
```

## Step 4: Roll out gradually

1. Set `availability` to `hidden` — only operators can see it (for internal testing)
2. Test via admin panel: `/admin/games`
3. Set to `beta` — visible to users with `beta_tester` role
4. Gather feedback
5. Set to `enabled` — visible to all players

Change availability in the admin panel at `/admin/games/{game}` → Set Availability.

## AbstractGameModule helpers

Extending `AbstractGameModule` provides helpers for managing session state:

| Helper | Description |
|--------|-------------|
| `getConfig($session)` | Returns `session_config` array |
| `updateConfig($session, $newState)` | Merges `$newState` into config and saves |
| `setConfig($session, $state)` | Replaces entire config and saves |

All default method implementations are no-ops or safe defaults — override only what you need.

## Important constraints

- **Stateless modules**: modules must not hold state on the instance. All game state goes in `session->session_config`.
- **No platform changes**: a new game module must never require changes to the platform core.
- **Slug must match**: `getSlug()` must return the same value as `games.slug`.
- **One module per slug**: the registry will throw if you register two modules with the same slug.
- **`onSessionStart` may return false**: to prevent a session from starting (rare edge case).

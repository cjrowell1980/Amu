<?php

namespace App\Providers;

use App\Models\GameRoom;
use App\Models\GameSession;
use App\Modules\ExampleGame\ExampleGameModule;
use App\Policies\GameRoomPolicy;
use App\Policies\GameSessionPolicy;
use App\Services\AuditService;
use App\Services\GameRegistryService;
use App\Services\RoomLifecycleService;
use App\Services\SessionLifecycleService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GameRegistryService::class);
        $this->app->singleton(AuditService::class);
        $this->app->singleton(RoomLifecycleService::class);
        $this->app->singleton(SessionLifecycleService::class);
    }

    public function boot(): void
    {
        $this->registerPolicies();
        $this->registerGameModules();
    }

    private function registerPolicies(): void
    {
        Gate::policy(GameRoom::class, GameRoomPolicy::class);
        Gate::policy(GameSession::class, GameSessionPolicy::class);
    }

    /**
     * Register all game module implementations with the platform registry.
     *
     * Add new game modules here as they are built. The registry maps
     * slug → module instance so the platform can resolve game logic at runtime.
     *
     * To gradually roll out a game:
     *   1. Set availability to 'hidden' in the games table (operator-only)
     *   2. Test via admin panel
     *   3. Move to 'beta' to let beta-tester role access it
     *   4. Set to 'enabled' for all players
     */
    private function registerGameModules(): void
    {
        $registry = $this->app->make(GameRegistryService::class);

        // ── Register modules below ──────────────────────────────────────────
        $registry->register(new ExampleGameModule());

        // Future entries look like:
        // $registry->register(new \App\Modules\Blackjack\BlackjackModule());
        // $registry->register(new \App\Modules\Poker\PokerModule());
        // $registry->register(new \App\Modules\Trivia\TriviaModule());
        // ───────────────────────────────────────────────────────────────────
    }
}

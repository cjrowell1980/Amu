<?php

namespace App\Providers;

use App\Modules\ExampleGame\ExampleGameModule;
use App\Services\GameRegistryService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // GameRegistryService is a singleton — modules register into it once.
        $this->app->singleton(GameRegistryService::class);
    }

    public function boot(): void
    {
        $this->registerGameModules();
    }

    /**
     * Register all game module implementations with the platform registry.
     *
     * Add new game modules here as they are built. The registry maps
     * slug → module instance so the platform can resolve game logic at runtime.
     */
    private function registerGameModules(): void
    {
        $registry = $this->app->make(GameRegistryService::class);

        // ── Register modules below ──────────────────────────────────────────
        $registry->register(new ExampleGameModule());

        // Future entries look like:
        // $registry->register(new \App\Modules\Poker\PokerModule());
        // $registry->register(new \App\Modules\Trivia\TriviaModule());
        // ───────────────────────────────────────────────────────────────────
    }
}

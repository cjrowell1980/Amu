<?php

namespace Amu\Blackjack;

use Amu\Blackjack\Services\BlackjackRuleEngine;
use Amu\Blackjack\Services\BlackjackSettlementService;
use Amu\Blackjack\Services\BlackjackTableManager;
use Amu\Core\Contracts\ModuleRegistry;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class BlackjackServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/blackjack.php', 'blackjack');

        $this->app->singleton(BlackjackGameModule::class);
        $this->app->singleton(BlackjackRuleEngine::class);
        $this->app->singleton(BlackjackSettlementService::class);
        $this->app->singleton(BlackjackTableManager::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Route::group([], __DIR__.'/../routes/api.php');

        Event::listen(MigrationsEnded::class, fn () => $this->registerModule());
        $this->app->afterResolving(ModuleRegistry::class, fn () => $this->registerModule());
    }

    private function registerModule(): void
    {
        if (! Schema::hasTable('game_modules')) {
            return;
        }

        $registry = $this->app->make(ModuleRegistry::class);
        $record = $registry->register($this->app->make(BlackjackGameModule::class));

        if (config('blackjack.auto_enable', true) && ! $record->enabled) {
            $registry->enable($record);
        }
    }
}

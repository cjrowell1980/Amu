<?php

namespace Amu\Core;

use Amu\Core\Contracts\ModuleRegistry;
use Amu\Core\Contracts\RoomManager;
use Amu\Core\Contracts\SessionManager;
use Amu\Core\Contracts\WalletManager;
use Amu\Core\Services\DatabaseModuleRegistry;
use Amu\Core\Services\EloquentRoomManager;
use Amu\Core\Services\EloquentSessionManager;
use Amu\Core\Services\EloquentWalletManager;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleRegistry::class, DatabaseModuleRegistry::class);
        $this->app->singleton(RoomManager::class, EloquentRoomManager::class);
        $this->app->singleton(SessionManager::class, EloquentSessionManager::class);
        $this->app->singleton(WalletManager::class, EloquentWalletManager::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'core');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
    }
}

<?php

namespace App\Providers;

use App\Models\GameRoom;
use App\Policies\GameRoomPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Telescope\Telescope;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        GameRoom::class => GameRoomPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Only admin/operator can access Horizon.
        Horizon::auth(function ($request) {
            return $request->user()?->hasRole(['admin', 'operator']) ?? false;
        });
    }
}

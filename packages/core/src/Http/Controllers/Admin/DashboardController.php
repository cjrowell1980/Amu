<?php

namespace Amu\Core\Http\Controllers\Admin;

use Amu\Core\Models\GameModule;
use Amu\Core\Models\GameRoom;
use Amu\Core\Models\GameSession;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('core::admin.dashboard', [
            'moduleCount' => GameModule::query()->count(),
            'activeRoomCount' => GameRoom::query()->whereIn('status', ['waiting', 'active'])->count(),
            'sessionCount' => GameSession::query()->count(),
        ]);
    }
}

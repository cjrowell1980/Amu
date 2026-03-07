<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameRoom;
use App\Models\GameSession;
use App\Models\User;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|operator']);
    }

    public function index()
    {
        $stats = [
            'users' => User::count(),
            'games' => Game::count(),
            'active_rooms' => GameRoom::whereIn('status', ['waiting', 'in_game'])->count(),
            'active_sessions' => GameSession::whereIn('status', ['active', 'starting'])->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}

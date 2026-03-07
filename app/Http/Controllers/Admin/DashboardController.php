<?php

namespace App\Http\Controllers\Admin;

use App\Enums\GameAvailability;
use App\Enums\RoomStatus;
use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Game;
use App\Models\GameRoom;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

class DashboardController extends Controller
{

    public function index()
    {
        $stats = [
            'total_users'     => User::count(),
            'total_games'     => Game::count(),
            'enabled_games'   => Game::where('availability', GameAvailability::Enabled->value)->count(),
            'waiting_rooms'   => GameRoom::where('status', RoomStatus::Waiting->value)->count(),
            'active_rooms'    => GameRoom::whereIn('status', [
                RoomStatus::Waiting->value,
                RoomStatus::Ready->value,
                RoomStatus::Starting->value,
                RoomStatus::InProgress->value,
            ])->count(),
            'active_sessions' => GameSession::whereIn('status', [
                SessionStatus::Active->value,
                SessionStatus::Starting->value,
                SessionStatus::Paused->value,
            ])->count(),
        ];

        $recentAuditLogs = AuditLog::with('user')
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentAuditLogs'));
    }
}

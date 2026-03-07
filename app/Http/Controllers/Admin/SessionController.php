<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Session\CancelSessionAction;
use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Models\GameSession;
use Illuminate\Http\Request;

class SessionController extends Controller
{

    public function index(Request $request)
    {
        $query = GameSession::with(['game', 'room'])
            ->withCount('participants');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($gameId = $request->query('game_id')) {
            $query->where('game_id', $gameId);
        }

        $sessions = $query->latest()->paginate(30)->withQueryString();
        $statuses = SessionStatus::cases();

        return view('admin.sessions.index', compact('sessions', 'statuses'));
    }

    public function show(GameSession $session)
    {
        $session->load(['game', 'room.host.profile', 'participants.user.profile']);

        return view('admin.sessions.show', compact('session'));
    }

    /**
     * Force-cancel an active session (operator action).
     */
    public function cancel(GameSession $session, CancelSessionAction $action)
    {
        $this->authorize('forceEnd', $session);

        if ($session->status->isTerminal()) {
            return back()->with('error', 'Session is already in a terminal state.');
        }

        $action->execute($session, reason: 'operator_cancelled');

        return redirect()->route('admin.sessions.index')
            ->with('success', "Session [{$session->uuid}] has been cancelled.");
    }
}

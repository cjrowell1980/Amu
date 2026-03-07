<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameSession;

class SessionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|operator']);
    }

    public function index()
    {
        $sessions = GameSession::with(['game', 'room'])
            ->withCount('participants')
            ->latest()
            ->paginate(30);

        return view('admin.sessions.index', compact('sessions'));
    }

    public function show(GameSession $session)
    {
        $session->load(['game', 'room', 'participants.user.profile']);

        return view('admin.sessions.show', compact('session'));
    }
}

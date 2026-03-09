<?php

namespace Amu\Core\Http\Controllers\Admin;

use Amu\Core\Models\GameSession;
use Illuminate\Routing\Controller;

class SessionController extends Controller
{
    public function index()
    {
        return view('core::admin.sessions.index', [
            'sessions' => GameSession::query()->with(['module', 'room'])->latest()->get(),
        ]);
    }
}

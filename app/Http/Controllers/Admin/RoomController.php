<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameRoom;

class RoomController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|operator']);
    }

    public function index()
    {
        $rooms = GameRoom::with(['game', 'host'])
            ->withCount('activeMembers')
            ->latest()
            ->paginate(30);

        return view('admin.rooms.index', compact('rooms'));
    }

    public function show(GameRoom $room)
    {
        $room->load(['game', 'host.profile', 'activeMembers.user.profile', 'sessions']);

        return view('admin.rooms.show', compact('room'));
    }
}

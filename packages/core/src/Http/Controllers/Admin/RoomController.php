<?php

namespace Amu\Core\Http\Controllers\Admin;

use Amu\Core\Models\GameRoom;
use Illuminate\Routing\Controller;

class RoomController extends Controller
{
    public function index()
    {
        return view('core::admin.rooms.index', [
            'rooms' => GameRoom::query()->with('module')->latest()->get(),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Room\LeaveRoomAction;
use App\Enums\RoomStatus;
use App\Http\Controllers\Controller;
use App\Models\GameRoom;
use App\Services\AuditService;
use App\Services\RoomLifecycleService;
use Illuminate\Http\Request;

class RoomController extends Controller
{

    public function index(Request $request)
    {
        $query = GameRoom::with(['game', 'host'])
            ->withCount('activeMembers');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($gameId = $request->query('game_id')) {
            $query->where('game_id', $gameId);
        }

        $rooms    = $query->latest()->paginate(30)->withQueryString();
        $statuses = RoomStatus::cases();

        return view('admin.rooms.index', compact('rooms', 'statuses'));
    }

    public function show(GameRoom $room)
    {
        $room->load(['game', 'host.profile', 'activeMembers.user.profile', 'sessions.participants']);

        return view('admin.rooms.show', compact('room'));
    }

    /**
     * Force-close a room (operator action — graceful closure).
     */
    public function close(GameRoom $room, RoomLifecycleService $lifecycle)
    {
        $this->authorize('forceClose', $room);

        if ($room->status->isTerminal()) {
            return back()->with('error', 'Room is already in a terminal state.');
        }

        $lifecycle->closeRoom($room, actor: auth()->user(), reason: 'operator_closed');

        return redirect()->route('admin.rooms.index')
            ->with('success', "Room #{$room->id} has been closed.");
    }

    /**
     * Force-cancel a room (operator action).
     */
    public function cancel(GameRoom $room, RoomLifecycleService $lifecycle)
    {
        $this->authorize('forceClose', $room);

        if ($room->status->isTerminal()) {
            return back()->with('error', 'Room is already in a terminal state.');
        }

        $lifecycle->cancelRoom($room, actor: auth()->user(), reason: 'operator_cancelled');

        return redirect()->route('admin.rooms.index')
            ->with('success', "Room #{$room->id} has been cancelled.");
    }
}

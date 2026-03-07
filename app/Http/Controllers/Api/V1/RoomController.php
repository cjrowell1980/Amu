<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Room\CreateRoomAction;
use App\Actions\Room\JoinRoomAction;
use App\Actions\Room\LeaveRoomAction;
use App\Events\Room\RoomReadyStateChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\Room\CreateRoomRequest;
use App\Http\Requests\Room\JoinRoomRequest;
use App\Http\Resources\GameRoomResource;
use App\Models\Game;
use App\Models\GameRoom;
use App\Models\GameRoomMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RoomController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $rooms = GameRoom::query()
            ->with(['game', 'host.profile'])
            ->withCount('activeMembers')
            ->publiclyVisible()
            ->waiting()
            ->when($request->game_id, fn ($q, $id) => $q->where('game_id', $id))
            ->latest()
            ->paginate(20);

        return GameRoomResource::collection($rooms);
    }

    public function show(GameRoom $room): JsonResponse
    {
        $this->authorize('view', $room);

        $room->load(['game', 'host.profile', 'activeMembers.user.profile']);

        return response()->json(new GameRoomResource($room));
    }

    public function store(CreateRoomRequest $request, CreateRoomAction $action): JsonResponse
    {
        $this->authorize('create', GameRoom::class);

        $game = Game::findOrFail($request->game_id);

        if (! $game->enabled) {
            return response()->json(['message' => 'This game is not currently available.'], 422);
        }

        $room = $action->execute($request->user(), $game, $request->validated());

        return response()->json(new GameRoomResource($room), 201);
    }

    public function join(JoinRoomRequest $request, GameRoom $room, JoinRoomAction $action): JsonResponse
    {
        $this->authorize('join', $room);

        $member = $action->execute($request->user(), $room, $request->password);

        return response()->json([
            'message' => 'Joined room successfully.',
            'room' => new GameRoomResource($room->fresh(['game', 'host.profile', 'activeMembers.user.profile'])),
        ]);
    }

    public function leave(Request $request, GameRoom $room, LeaveRoomAction $action): JsonResponse
    {
        $this->authorize('leave', $room);

        $action->execute($request->user(), $room);

        return response()->json(['message' => 'Left room successfully.']);
    }

    public function toggleReady(Request $request, GameRoom $room): JsonResponse
    {
        $this->authorize('toggleReady', $room);

        $member = GameRoomMember::where('game_room_id', $room->id)
            ->where('user_id', $request->user()->id)
            ->whereNull('left_at')
            ->firstOrFail();

        $member->update(['is_ready' => ! $member->is_ready]);

        event(new RoomReadyStateChanged($room, $request->user()->id, $member->is_ready));

        return response()->json([
            'is_ready' => $member->is_ready,
            'message' => $member->is_ready ? 'You are ready.' : 'You are not ready.',
        ]);
    }
}

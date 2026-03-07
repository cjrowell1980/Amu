<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Room\CreateRoomAction;
use App\Actions\Room\JoinRoomAction;
use App\Actions\Room\LeaveRoomAction;
use App\Actions\Session\CreateSessionAction;
use App\Enums\RoomMemberRole;
use App\Enums\RoomStatus;
use App\Events\Room\RoomReadyStateChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\Room\CreateRoomRequest;
use App\Http\Requests\Room\JoinRoomRequest;
use App\Http\Resources\GameRoomResource;
use App\Http\Resources\GameSessionResource;
use App\Models\Game;
use App\Models\GameRoom;
use App\Models\GameRoomMember;
use App\Services\AuditService;
use App\Services\RoomLifecycleService;
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
            ->openForJoining()
            ->when($request->game_id, fn ($q, $id) => $q->where('game_id', $id))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20);

        return GameRoomResource::collection($rooms);
    }

    public function show(GameRoom $room): JsonResponse
    {
        $this->authorize('view', $room);

        $room->load(['game', 'host.profile', 'activeMembers.user.profile']);

        return response()->json(['data' => new GameRoomResource($room)]);
    }

    public function store(CreateRoomRequest $request, CreateRoomAction $action): JsonResponse
    {
        $this->authorize('create', GameRoom::class);

        $game = Game::findOrFail($request->game_id);

        if (! $game->isEnabled()) {
            return response()->json([
                'message' => 'This game is not currently available.',
                'error'   => 'game_unavailable',
            ], 422);
        }

        $room = $action->execute($request->user(), $game, $request->validated());

        return response()->json(['data' => new GameRoomResource($room)], 201);
    }

    public function join(JoinRoomRequest $request, GameRoom $room, JoinRoomAction $action): JsonResponse
    {
        $this->authorize('join', $room);

        $action->execute(
            $request->user(),
            $room,
            $request->input('password'),
            $request->boolean('as_spectator'),
        );

        return response()->json([
            'message' => 'Joined room successfully.',
            'data'    => new GameRoomResource($room->fresh(['game', 'host.profile', 'activeMembers.user.profile'])),
        ]);
    }

    public function leave(Request $request, GameRoom $room, LeaveRoomAction $action): JsonResponse
    {
        $this->authorize('leave', $room);

        $action->execute($request->user(), $room);

        return response()->json(['message' => 'Left room successfully.']);
    }

    public function toggleReady(Request $request, GameRoom $room, AuditService $audit): JsonResponse
    {
        $this->authorize('toggleReady', $room);

        $member = GameRoomMember::where('game_room_id', $room->id)
            ->where('user_id', $request->user()->id)
            ->whereNull('left_at')
            ->firstOrFail();

        if (! $member->canToggleReady()) {
            return response()->json([
                'message' => 'Spectators cannot toggle ready state.',
                'error'   => 'spectator_cannot_ready',
            ], 422);
        }

        $member->update(['is_ready' => ! $member->is_ready]);

        $audit->readyStateChanged($room, $request->user(), $member->is_ready);
        event(new RoomReadyStateChanged($room, $request->user()->id, $member->is_ready));

        // Recalculate room ready status
        app(RoomLifecycleService::class)->recalculateReadyStatus($room);

        return response()->json([
            'data' => [
                'is_ready'    => $member->is_ready,
                'room_status' => $room->fresh()->status->value,
            ],
            'message' => $member->is_ready ? 'You are ready.' : 'You are not ready.',
        ]);
    }

    /**
     * Host starts the session for their room.
     */
    public function startSession(Request $request, GameRoom $room, CreateSessionAction $action): JsonResponse
    {
        $this->authorize('startSession', $room);

        $session = $action->execute($room);

        return response()->json([
            'message' => 'Session created and starting.',
            'data'    => new GameSessionResource($session),
        ], 201);
    }
}

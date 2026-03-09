<?php

namespace Amu\Core\Http\Controllers\Api\V1;

use Amu\Core\Contracts\ModuleRegistry;
use Amu\Core\Contracts\RoomManager;
use Amu\Core\Http\Requests\CreateRoomRequest;
use Amu\Core\Http\Requests\JoinRoomRequest;
use Amu\Core\Http\Requests\ReadyStateRequest;
use Amu\Core\Http\Resources\GameRoomResource;
use Amu\Core\Models\GameRoom;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class RoomController extends Controller
{
    public function index(RoomManager $rooms)
    {
        return GameRoomResource::collection($rooms->listVisibleFor(request()->user()));
    }

    public function store(CreateRoomRequest $request, ModuleRegistry $modules, RoomManager $rooms): JsonResponse
    {
        $module = $modules->findBySlug($request->string('game_module_slug')->toString());

        abort_unless($module?->enabled, 422, 'Module is not enabled.');

        $room = $rooms->create($module, $request->user(), $request->validated());

        return response()->json([
            'data' => new GameRoomResource($room),
        ], 201);
    }

    public function show(int $room): GameRoomResource
    {
        $room = GameRoom::query()->with(['module', 'players'])->findOrFail($room);

        abort_unless(
            $room->visibility->value === 'public'
            || $room->players()->where('user_id', request()->user()->id)->exists(),
            403,
        );

        return new GameRoomResource($room);
    }

    public function join(JoinRoomRequest $request, int $room, RoomManager $rooms): JsonResponse
    {
        $room = GameRoom::query()->findOrFail($room);

        $updated = $rooms->join(
            $room,
            $request->user(),
            $request->input('participation', 'joined'),
        );

        return response()->json([
            'message' => 'Joined room successfully.',
            'data' => new GameRoomResource($updated),
        ]);
    }

    public function leave(int $room, RoomManager $rooms): JsonResponse
    {
        $room = GameRoom::query()->findOrFail($room);
        $updated = $rooms->leave($room, request()->user());

        return response()->json([
            'message' => 'Left room successfully.',
            'data' => new GameRoomResource($updated),
        ]);
    }

    public function ready(ReadyStateRequest $request, int $room, RoomManager $rooms): JsonResponse
    {
        $room = GameRoom::query()->findOrFail($room);
        $updated = $rooms->setReady($room, $request->user(), $request->boolean('is_ready'));

        return response()->json([
            'data' => new GameRoomResource($updated),
        ]);
    }
}

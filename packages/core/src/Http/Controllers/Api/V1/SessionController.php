<?php

namespace Amu\Core\Http\Controllers\Api\V1;

use Amu\Core\Contracts\SessionManager;
use Amu\Core\Enums\SessionStatus;
use Amu\Core\Http\Requests\SessionTransitionRequest;
use Amu\Core\Http\Resources\GameSessionResource;
use Amu\Core\Models\GameRoom;
use Amu\Core\Models\GameSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class SessionController extends Controller
{
    public function store(int $room, SessionManager $sessions): JsonResponse
    {
        $room = GameRoom::query()->findOrFail($room);
        $session = $sessions->create($room, ['status' => SessionStatus::Waiting]);

        return response()->json([
            'data' => new GameSessionResource($session),
        ], 201);
    }

    public function show(int $session): GameSessionResource
    {
        $session = GameSession::query()->with('module')->findOrFail($session);

        return new GameSessionResource($session);
    }

    public function transition(SessionTransitionRequest $request, int $session, SessionManager $sessions): JsonResponse
    {
        $session = GameSession::query()->findOrFail($session);
        $updated = $sessions->transition(
            $session,
            SessionStatus::from($request->string('status')->toString()),
        );

        return response()->json([
            'data' => new GameSessionResource($updated),
        ]);
    }
}

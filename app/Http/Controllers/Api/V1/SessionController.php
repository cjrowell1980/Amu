<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Session\StartSessionAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\GameSessionResource;
use App\Models\GameSession;
use App\Services\GameRegistryService;
use App\Services\SessionLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function show(GameSession $session): JsonResponse
    {
        $this->authorize('view', $session);

        $session->load(['game', 'room', 'participants.user.profile']);

        return response()->json(['data' => new GameSessionResource($session)]);
    }

    /**
     * Start a created/pending session (called by host after create).
     */
    public function start(GameSession $session, StartSessionAction $action): JsonResponse
    {
        $this->authorize('start', $session);

        $session = $action->execute($session);

        return response()->json([
            'message' => 'Session started.',
            'data'    => new GameSessionResource($session),
        ]);
    }

    /**
     * Get the public game state for a running session.
     * The game module provides this via getPublicState().
     */
    public function publicState(GameSession $session, GameRegistryService $registry): JsonResponse
    {
        $this->authorize('view', $session);

        if (! $session->isActive()) {
            return response()->json([
                'message' => 'Session is not currently active.',
                'error'   => 'session_not_active',
            ], 409);
        }

        $state = [];
        if ($registry->has($session->game->slug)) {
            $state = $registry->resolve($session->game->slug)->getPublicState($session);
        }

        return response()->json(['data' => $state]);
    }

    /**
     * Get the private game state for the authenticated player.
     */
    public function privateState(GameSession $session, GameRegistryService $registry): JsonResponse
    {
        $this->authorize('view', $session);

        if (! $session->isActive()) {
            return response()->json([
                'message' => 'Session is not currently active.',
                'error'   => 'session_not_active',
            ], 409);
        }

        $participant = $session->participants()
            ->where('user_id', auth()->id())
            ->first();

        if (! $participant) {
            return response()->json(['message' => 'You are not a participant in this session.'], 403);
        }

        $state = [];
        if ($registry->has($session->game->slug)) {
            $state = $registry->resolve($session->game->slug)->getPrivateState($session, $participant);
        }

        return response()->json(['data' => $state]);
    }

    /**
     * Submit a player action to the game module.
     */
    public function action(Request $request, GameSession $session, GameRegistryService $registry): JsonResponse
    {
        $this->authorize('act', $session);

        if (! $session->isActive()) {
            return response()->json([
                'message' => 'Session is not currently active.',
                'error'   => 'session_not_active',
            ], 409);
        }

        $participant = $session->participants()
            ->where('user_id', auth()->id())
            ->whereNull('left_at')
            ->first();

        if (! $participant) {
            return response()->json(['message' => 'You are not an active participant in this session.'], 403);
        }

        if (! $registry->has($session->game->slug)) {
            return response()->json(['message' => 'No game module registered for this game.'], 503);
        }

        $module = $registry->resolve($session->game->slug);
        $result = $module->handlePlayerAction($session, $participant, $request->all());

        // Update heartbeat
        app(SessionLifecycleService::class)->heartbeat($participant);

        return response()->json(['data' => $result]);
    }

    /**
     * Reconnect endpoint: exchange a reconnect_token for fresh session state.
     */
    public function reconnect(Request $request, SessionLifecycleService $lifecycle): JsonResponse
    {
        $request->validate(['token' => 'required|string|size:64']);

        $participant = $lifecycle->findByReconnectToken($request->input('token'));

        if (! $participant) {
            return response()->json(['message' => 'Invalid or expired reconnect token.'], 401);
        }

        $lifecycle->markReconnected($participant);

        $session = $participant->session->load(['game', 'room', 'participants.user.profile']);

        return response()->json([
            'message' => 'Reconnected successfully.',
            'data'    => new GameSessionResource($session),
        ]);
    }
}

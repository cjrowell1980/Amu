<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GameController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $games = Game::enabled()
            ->orderBy('name')
            ->get();

        return GameResource::collection($games);
    }

    public function show(Game $game): JsonResponse
    {
        if (! $game->enabled) {
            abort(404);
        }

        return response()->json(new GameResource($game));
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\GameAvailability;
use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GameController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $user  = auth()->user();
        $query = Game::query()->orderBy('name');

        if ($user->hasRole(['admin', 'operator'])) {
            // Operators see everything except disabled
            $query->visibleToOperators();
        } elseif ($user->hasPermissionTo('access beta games') || $user->hasRole('moderator')) {
            // Beta testers see enabled + beta
            $query->availableForBeta();
        } else {
            // Regular players see only enabled
            $query->enabled();
        }

        return GameResource::collection($query->get());
    }

    public function show(Game $game): JsonResponse
    {
        $user = auth()->user();

        $canSee = match(true) {
            $user->hasRole(['admin', 'operator']) => ! $game->isDisabled(),
            $user->hasPermissionTo('access beta games') => $game->isEnabled() || $game->isBeta(),
            default => $game->isEnabled(),
        };

        if (! $canSee) {
            abort(404);
        }

        return response()->json(['data' => new GameResource($game)]);
    }
}

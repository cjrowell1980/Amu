<?php

namespace Amu\Blackjack\Http\Controllers\Api\V1;

use Amu\Blackjack\Http\Requests\PlaceBetRequest;
use Amu\Blackjack\Http\Resources\BlackjackRoundResource;
use Amu\Blackjack\Models\BlackjackRound;
use Amu\Blackjack\Models\BlackjackTable;
use Amu\Blackjack\Services\BlackjackSettlementService;
use Amu\Blackjack\Services\BlackjackTableManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class BlackjackRoundController extends Controller
{
    public function store(int $table, BlackjackTableManager $tables): JsonResponse
    {
        $table = BlackjackTable::query()->with(['room', 'seats'])->findOrFail($table);
        abort_unless($table->room->host_user_id === request()->user()->id || request()->user()->hasRole('admin'), 403);

        $round = $tables->startRound($table);

        return response()->json(['data' => new BlackjackRoundResource($round->load(['hands', 'bets']))], 201);
    }

    public function bet(PlaceBetRequest $request, int $round, BlackjackTableManager $tables): JsonResponse
    {
        $round = BlackjackRound::query()->with(['table.seats', 'bets', 'hands'])->findOrFail($round);
        $round = $tables->placeBet($round, $request->user(), (int) $request->integer('amount'));

        return response()->json(['data' => new BlackjackRoundResource($round)]);
    }

    public function deal(int $round, BlackjackTableManager $tables): JsonResponse
    {
        $round = BlackjackRound::query()->with(['table.room', 'hands', 'bets'])->findOrFail($round);
        abort_unless($round->table->room->host_user_id === request()->user()->id || request()->user()->hasRole('admin'), 403);

        $round = $tables->deal($round);

        return response()->json(['data' => new BlackjackRoundResource($round->load(['hands', 'bets']))]);
    }

    public function hit(int $round, BlackjackTableManager $tables): JsonResponse
    {
        $round = BlackjackRound::query()->with(['table', 'hands', 'bets', 'currentTurnSeat'])->findOrFail($round);
        $round = $tables->hit($round, request()->user());

        return response()->json(['data' => new BlackjackRoundResource($round->load(['hands', 'bets']))]);
    }

    public function stand(int $round, BlackjackTableManager $tables): JsonResponse
    {
        $round = BlackjackRound::query()->with(['table', 'hands', 'bets', 'currentTurnSeat'])->findOrFail($round);
        $round = $tables->stand($round, request()->user());

        return response()->json(['data' => new BlackjackRoundResource($round->load(['hands', 'bets']))]);
    }

    public function settle(int $round, BlackjackSettlementService $settlements): JsonResponse
    {
        $round = BlackjackRound::query()->with(['table.room', 'hands.user', 'bets.user'])->findOrFail($round);
        abort_unless($round->table->room->host_user_id === request()->user()->id || request()->user()->hasRole('admin'), 403);

        $round = $settlements->settle($round);

        return response()->json(['data' => new BlackjackRoundResource($round->load(['hands', 'bets']))]);
    }
}

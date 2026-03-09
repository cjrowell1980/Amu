<?php

namespace Amu\Blackjack\Http\Controllers\Api\V1;

use Amu\Blackjack\Http\Requests\OpenTableRequest;
use Amu\Blackjack\Http\Resources\BlackjackTableResource;
use Amu\Blackjack\Models\BlackjackTable;
use Amu\Blackjack\Services\BlackjackTableManager;
use Amu\Core\Models\GameRoom;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class BlackjackTableController extends Controller
{
    public function open(OpenTableRequest $request, int $room, BlackjackTableManager $tables): JsonResponse
    {
        $room = GameRoom::query()->with('module')->findOrFail($room);
        abort_unless($room->module->slug === 'blackjack', 422, 'Room is not a Blackjack room.');
        abort_unless($room->host_user_id === $request->user()->id || $request->user()->hasRole('admin'), 403);

        $table = $tables->open($room);

        return response()->json(['data' => new BlackjackTableResource($table)], 201);
    }

    public function show(int $table): BlackjackTableResource
    {
        $table = BlackjackTable::query()->with(['seats.user', 'rounds.hands', 'rounds.bets'])->findOrFail($table);

        return new BlackjackTableResource($table);
    }

    public function seat(int $table, BlackjackTableManager $tables): JsonResponse
    {
        $table = BlackjackTable::query()->with(['room', 'seats.user', 'rounds'])->findOrFail($table);
        $table = $tables->seatPlayer($table, request()->user());

        return response()->json(['data' => new BlackjackTableResource($table)]);
    }
}

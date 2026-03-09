<?php

namespace Amu\Blackjack\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlackjackTableResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $latestRound = $this->rounds->first();

        return [
            'id' => $this->id,
            'room_id' => $this->game_room_id,
            'session_id' => $this->game_session_id,
            'status' => $this->status,
            'decks' => $this->decks,
            'dealer_hits_soft_17' => $this->dealer_hits_soft_17,
            'seats' => $this->seats->map(fn ($seat) => [
                'id' => $seat->id,
                'seat_number' => $seat->seat_number,
                'user_id' => $seat->user_id,
                'user_name' => $seat->user?->name,
            ])->values(),
            'latest_round' => $latestRound ? new BlackjackRoundResource($latestRound) : null,
        ];
    }
}

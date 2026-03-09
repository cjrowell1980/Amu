<?php

namespace Amu\Blackjack\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlackjackRoundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $dealerCards = $this->status === 'settled'
            ? $this->dealer_cards
            : [$this->dealer_cards[0] ?? null, count($this->dealer_cards ?? []) > 1 ? 'hidden' : null];

        return [
            'id' => $this->id,
            'status' => $this->status,
            'dealer_cards' => array_values(array_filter($dealerCards, fn ($card) => $card !== null)),
            'dealer_value' => $this->status === 'settled' ? $this->dealer_value : null,
            'current_turn_seat_id' => $this->current_turn_seat_id,
            'hands' => $this->hands->map(fn ($hand) => [
                'user_id' => $hand->user_id,
                'seat_id' => $hand->blackjack_seat_id,
                'cards' => $hand->cards,
                'value' => $hand->value,
                'status' => $hand->status,
                'outcome' => $hand->outcome,
                'payout' => $hand->payout,
            ])->values(),
            'bets' => $this->bets->map(fn ($bet) => [
                'user_id' => $bet->user_id,
                'amount' => $bet->amount,
                'status' => $bet->status,
                'payout' => $bet->payout,
            ])->values(),
            'settled_at' => $this->settled_at,
        ];
    }
}

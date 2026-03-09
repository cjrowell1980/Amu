<?php

namespace Amu\Blackjack\Services;

use Amu\Blackjack\Models\BlackjackAction;
use Amu\Blackjack\Models\BlackjackBet;
use Amu\Blackjack\Models\BlackjackHand;
use Amu\Blackjack\Models\BlackjackRound;
use Amu\Blackjack\Models\BlackjackSeat;
use Amu\Blackjack\Models\BlackjackTable;
use Amu\Core\Contracts\RoomManager;
use Amu\Core\Contracts\SessionManager;
use Amu\Core\Contracts\WalletManager;
use Amu\Core\Enums\SessionStatus;
use Amu\Core\Models\GameRoom;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BlackjackTableManager
{
    public function __construct(
        private readonly RoomManager $rooms,
        private readonly SessionManager $sessions,
        private readonly WalletManager $wallets,
        private readonly BlackjackRuleEngine $rules,
        private readonly BlackjackSettlementService $settlements,
    ) {
    }

    public function open(GameRoom $room): BlackjackTable
    {
        if ($room->module->slug !== 'blackjack') {
            throw new RuntimeException('Room is not a Blackjack room.');
        }

        return DB::transaction(function () use ($room) {
            $table = BlackjackTable::query()->firstOrCreate(
                ['game_room_id' => $room->id],
                [
                    'decks' => config('blackjack.decks', 6),
                    'dealer_hits_soft_17' => config('blackjack.dealer_hits_soft_17', false),
                    'status' => 'open',
                    'shoe_state' => $this->rules->createShoe((int) config('blackjack.decks', 6)),
                ],
            );

            if ($table->game_session_id === null) {
                $session = $this->sessions->create($room, ['status' => SessionStatus::Pending]);
                $session = $this->sessions->transition($session, SessionStatus::Waiting);
                $session = $this->sessions->transition($session, SessionStatus::Active);
                $table->forceFill(['game_session_id' => $session->id])->save();
            }

            return $table->refresh()->load(['room.module', 'seats.user', 'rounds']);
        });
    }

    public function seatPlayer(BlackjackTable $table, User $user): BlackjackTable
    {
        return DB::transaction(function () use ($table, $user) {
            if (! $table->room->players()->where('user_id', $user->id)->exists()) {
                $this->rooms->join($table->room, $user, 'seated');
            }

            BlackjackSeat::query()->firstOrCreate(
                ['blackjack_table_id' => $table->id, 'user_id' => $user->id],
                [
                    'seat_number' => $this->nextSeatNumber($table),
                    'status' => 'active',
                ],
            );

            return $table->refresh()->load(['seats.user', 'room.module']);
        });
    }

    public function startRound(BlackjackTable $table): BlackjackRound
    {
        $activeRound = $table->rounds()->whereIn('status', ['betting', 'dealing', 'player_turn', 'dealer_turn'])->first();
        if ($activeRound) {
            throw new RuntimeException('A round is already active.');
        }

        if ($table->seats()->count() === 0) {
            throw new RuntimeException('No seated players are available.');
        }

        return BlackjackRound::query()->create([
            'blackjack_table_id' => $table->id,
            'status' => 'betting',
            'dealer_cards' => [],
            'dealer_value' => 0,
        ])->load(['table', 'bets', 'hands']);
    }

    public function placeBet(BlackjackRound $round, User $user, int $amount): BlackjackRound
    {
        if ($round->status !== 'betting') {
            throw new RuntimeException('Bets are closed for this round.');
        }

        $table = $round->table()->with('seats')->firstOrFail();
        $seat = $table->seats->firstWhere('user_id', $user->id);
        if (! $seat) {
            throw new RuntimeException('Player must be seated before betting.');
        }

        if ($amount < (int) config('blackjack.default_min_bet', 100) || $amount > (int) config('blackjack.default_max_bet', 10000)) {
            throw new RuntimeException('Bet amount is outside the allowed range.');
        }

        return DB::transaction(function () use ($round, $user, $amount) {
            if ($round->bets()->where('user_id', $user->id)->exists()) {
                throw new RuntimeException('Bet already placed for this round.');
            }

            $account = $this->wallets->accountFor($user, config('blackjack.wallet_type', 'credits'));
            $this->wallets->debit($account, $amount, [
                'reason' => 'blackjack-bet',
                'reference_type' => 'blackjack_round',
                'reference_id' => $round->id,
            ]);

            BlackjackBet::query()->create([
                'blackjack_round_id' => $round->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'status' => 'placed',
                'payout' => 0,
            ]);

            BlackjackAction::query()->create([
                'blackjack_round_id' => $round->id,
                'user_id' => $user->id,
                'action' => 'bet',
                'payload' => ['amount' => $amount],
            ]);

            return $round->refresh()->load(['bets', 'hands', 'table.seats.user']);
        });
    }

    public function deal(BlackjackRound $round): BlackjackRound
    {
        if ($round->status !== 'betting') {
            throw new RuntimeException('Round cannot be dealt in its current state.');
        }

        return DB::transaction(function () use ($round) {
            $round = BlackjackRound::query()->with(['table.seats', 'bets'])->lockForUpdate()->findOrFail($round->id);
            if ($round->bets->isEmpty()) {
                throw new RuntimeException('At least one bet is required to deal.');
            }

            $shoe = $this->ensureShoe($round->table);
            $orderedSeats = $round->table->seats()->whereIn('user_id', $round->bets->pluck('user_id'))->orderBy('seat_number')->get();

            foreach ($orderedSeats as $seat) {
                $cards = array_merge($this->rules->draw($shoe), $this->rules->draw($shoe));
                $evaluation = $this->rules->evaluate($cards);

                BlackjackHand::query()->create([
                    'blackjack_round_id' => $round->id,
                    'user_id' => $seat->user_id,
                    'blackjack_seat_id' => $seat->id,
                    'cards' => $cards,
                    'value' => $evaluation['value'],
                    'status' => $this->rules->isBlackjack($cards) ? 'blackjack' : 'active',
                    'is_blackjack' => $this->rules->isBlackjack($cards),
                    'is_bust' => false,
                    'outcome' => null,
                    'payout' => 0,
                ]);
            }

            $dealerCards = array_merge($this->rules->draw($shoe), $this->rules->draw($shoe));
            $dealerEvaluation = $this->rules->evaluate($dealerCards);

            $round->table->forceFill(['shoe_state' => $shoe])->save();
            $round->forceFill([
                'status' => 'player_turn',
                'dealer_cards' => $dealerCards,
                'dealer_value' => $dealerEvaluation['value'],
            ])->save();

            BlackjackAction::query()->create([
                'blackjack_round_id' => $round->id,
                'user_id' => null,
                'action' => 'deal',
                'payload' => ['dealer_up_card' => $dealerCards[0]],
            ]);

            return $this->advanceTurn($round->refresh()->load(['hands.seat', 'table']));
        });
    }

    public function hit(BlackjackRound $round, User $user): BlackjackRound
    {
        return DB::transaction(function () use ($round, $user) {
            $round = BlackjackRound::query()->with(['table', 'hands.seat', 'currentTurnSeat'])->lockForUpdate()->findOrFail($round->id);
            $hand = $this->currentHandFor($round, $user);
            $shoe = $this->ensureShoe($round->table);
            $card = $this->rules->draw($shoe)[0];
            $cards = array_merge($hand->cards, [$card]);
            $evaluation = $this->rules->evaluate($cards);

            $hand->forceFill([
                'cards' => $cards,
                'value' => $evaluation['value'],
                'is_bust' => $evaluation['value'] > 21,
                'status' => $evaluation['value'] > 21 ? 'bust' : ($evaluation['value'] === 21 ? 'stood' : 'active'),
            ])->save();

            $round->table->forceFill(['shoe_state' => $shoe])->save();

            BlackjackAction::query()->create([
                'blackjack_round_id' => $round->id,
                'user_id' => $user->id,
                'action' => 'hit',
                'payload' => ['card' => $card],
            ]);

            return $evaluation['value'] >= 21
                ? $this->advanceTurn($round->refresh()->load(['hands.seat', 'table']))
                : $round->refresh()->load(['hands', 'bets', 'table.seats.user']);
        });
    }

    public function stand(BlackjackRound $round, User $user): BlackjackRound
    {
        return DB::transaction(function () use ($round, $user) {
            $round = BlackjackRound::query()->with(['table', 'hands.seat', 'currentTurnSeat'])->lockForUpdate()->findOrFail($round->id);
            $hand = $this->currentHandFor($round, $user);

            $hand->forceFill(['status' => 'stood'])->save();

            BlackjackAction::query()->create([
                'blackjack_round_id' => $round->id,
                'user_id' => $user->id,
                'action' => 'stand',
                'payload' => [],
            ]);

            return $this->advanceTurn($round->refresh()->load(['hands.seat', 'table']));
        });
    }

    private function advanceTurn(BlackjackRound $round): BlackjackRound
    {
        $nextHand = $round->hands()
            ->where('status', 'active')
            ->orderBy('blackjack_seat_id')
            ->first();

        if ($nextHand) {
            $round->forceFill([
                'status' => 'player_turn',
                'current_turn_seat_id' => $nextHand->blackjack_seat_id,
            ])->save();

            return $round->refresh()->load(['hands.seat.user', 'bets', 'table.seats.user']);
        }

        return $this->resolveDealer($round);
    }

    private function resolveDealer(BlackjackRound $round): BlackjackRound
    {
        $round->refresh();
        $cards = $round->dealer_cards ?? [];
        $shoe = $this->ensureShoe($round->table);
        $hasCompetitivePlayer = $round->hands()->whereNotIn('status', ['bust', 'blackjack'])->exists();

        if ($hasCompetitivePlayer) {
            while ($this->rules->dealerShouldHit($cards, (bool) $round->table->dealer_hits_soft_17)) {
                $card = $this->rules->draw($shoe)[0];
                $cards[] = $card;

                BlackjackAction::query()->create([
                    'blackjack_round_id' => $round->id,
                    'user_id' => null,
                    'action' => 'dealer-hit',
                    'payload' => ['card' => $card],
                ]);
            }
        }

        $evaluation = $this->rules->evaluate($cards);

        $round->table->forceFill(['shoe_state' => $shoe])->save();
        $round->forceFill([
            'status' => 'dealer_turn',
            'dealer_cards' => $cards,
            'dealer_value' => $evaluation['value'],
            'current_turn_seat_id' => null,
        ])->save();

        return $this->settlements->settle($round);
    }

    private function currentHandFor(BlackjackRound $round, User $user): BlackjackHand
    {
        $seat = $round->currentTurnSeat;

        if (! $seat || $seat->user_id !== $user->id) {
            throw new RuntimeException('It is not this player\'s turn.');
        }

        return $round->hands->firstWhere('blackjack_seat_id', $seat->id)
            ?? throw new RuntimeException('No active hand found.');
    }

    private function ensureShoe(BlackjackTable $table): array
    {
        $shoe = $table->shoe_state ?? [];

        if (count($shoe) === 0) {
            $shoe = $this->rules->createShoe($table->decks);
        }

        return $shoe;
    }

    private function nextSeatNumber(BlackjackTable $table): int
    {
        return ((int) $table->seats()->max('seat_number')) + 1;
    }
}

<?php

namespace Amu\Blackjack\Services;

use Amu\Blackjack\Models\BlackjackBet;
use Amu\Blackjack\Models\BlackjackRound;
use Amu\Core\Contracts\WalletManager;
use Illuminate\Support\Facades\DB;

class BlackjackSettlementService
{
    public function __construct(private readonly WalletManager $wallets)
    {
    }

    public function settle(BlackjackRound $round): BlackjackRound
    {
        return DB::transaction(function () use ($round) {
            $round = BlackjackRound::query()
                ->with(['table.room', 'hands.user', 'bets.user'])
                ->lockForUpdate()
                ->findOrFail($round->id);

            if ($round->settled_at !== null) {
                return $round;
            }

            $summary = [];

            foreach ($round->hands as $hand) {
                $bet = $round->bets->firstWhere('user_id', $hand->user_id);
                if (! $bet instanceof BlackjackBet) {
                    continue;
                }

                [$outcome, $credit] = $this->resolveOutcome(
                    $hand->value,
                    $hand->is_blackjack,
                    $hand->is_bust,
                    $round->dealer_value,
                    $round->dealer_cards,
                    $bet->amount,
                );

                if ($bet->settled_at === null) {
                    if ($credit > 0) {
                        $account = $this->wallets->accountFor($hand->user, config('blackjack.wallet_type', 'credits'));
                        $this->wallets->credit($account, $credit, [
                            'reason' => 'blackjack-settlement',
                            'reference_type' => 'blackjack_round',
                            'reference_id' => $round->id,
                            'metadata' => ['outcome' => $outcome],
                        ]);
                    }

                    $bet->forceFill([
                        'status' => 'settled',
                        'payout' => $credit,
                        'settled_at' => now(),
                    ])->save();
                }

                $hand->forceFill([
                    'outcome' => $outcome,
                    'payout' => $credit,
                    'status' => in_array($outcome, ['win', 'blackjack'], true) ? 'won' : ($outcome === 'push' ? 'push' : 'lost'),
                ])->save();

                $summary[$hand->user_id] = [
                    'outcome' => $outcome,
                    'payout' => $credit,
                ];
            }

            $round->forceFill([
                'status' => 'settled',
                'outcome_summary' => $summary,
                'settled_at' => now(),
                'current_turn_seat_id' => null,
            ])->save();

            return $round->refresh()->load(['hands', 'bets', 'table']);
        });
    }

    private function resolveOutcome(
        int $playerValue,
        bool $playerBlackjack,
        bool $playerBust,
        int $dealerValue,
        array $dealerCards,
        int $betAmount,
    ): array {
        $dealerBlackjack = count($dealerCards) === 2 && $dealerValue === 21;
        $dealerBust = $dealerValue > 21;

        if ($playerBust) {
            return ['loss', 0];
        }

        if ($playerBlackjack && ! $dealerBlackjack) {
            $credit = (int) (($betAmount * config('blackjack.blackjack_payout_numerator', 5)) / config('blackjack.blackjack_payout_denominator', 2));

            return ['blackjack', $credit];
        }

        if ($dealerBust || $playerValue > $dealerValue) {
            return ['win', $betAmount * 2];
        }

        if ($playerValue === $dealerValue) {
            return ['push', $betAmount];
        }

        return ['loss', 0];
    }
}

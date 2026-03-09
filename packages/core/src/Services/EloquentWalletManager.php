<?php

namespace Amu\Core\Services;

use Amu\Core\Contracts\WalletManager;
use Amu\Core\Events\BalanceChanged;
use Amu\Core\Models\WalletAccount;
use Amu\Core\Models\WalletTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EloquentWalletManager implements WalletManager
{
    public function accountFor(User $user, string $type = 'credits'): WalletAccount
    {
        return WalletAccount::query()->firstOrCreate(
            ['user_id' => $user->id, 'type' => $type],
            ['balance' => 0],
        );
    }

    public function credit(WalletAccount $account, int $amount, array $context = []): WalletAccount
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Credit amount must be positive.');
        }

        return $this->apply($account, $amount, 'credit', $context);
    }

    public function debit(WalletAccount $account, int $amount, array $context = []): WalletAccount
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Debit amount must be positive.');
        }

        if ($account->balance < $amount) {
            throw new InvalidArgumentException('Insufficient balance.');
        }

        return $this->apply($account, -$amount, 'debit', $context);
    }

    private function apply(WalletAccount $account, int $delta, string $direction, array $context): WalletAccount
    {
        return DB::transaction(function () use ($account, $delta, $direction, $context) {
            $account->refresh();
            $account->forceFill(['balance' => $account->balance + $delta])->save();

            $transaction = WalletTransaction::query()->create([
                'wallet_account_id' => $account->id,
                'amount' => $delta,
                'balance_after' => $account->balance,
                'direction' => $direction,
                'reason' => $context['reason'] ?? null,
                'reference_type' => $context['reference_type'] ?? null,
                'reference_id' => $context['reference_id'] ?? null,
                'metadata' => $context['metadata'] ?? [],
            ]);

            event(new BalanceChanged($transaction));

            return $account->refresh();
        });
    }
}

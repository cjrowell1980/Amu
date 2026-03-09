<?php

namespace Amu\Core\Contracts;

use Amu\Core\Models\WalletAccount;
use App\Models\User;

interface WalletManager
{
    public function accountFor(User $user, string $type = 'credits'): WalletAccount;

    public function credit(WalletAccount $account, int $amount, array $context = []): WalletAccount;

    public function debit(WalletAccount $account, int $amount, array $context = []): WalletAccount;
}

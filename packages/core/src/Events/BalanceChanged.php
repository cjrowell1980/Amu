<?php

namespace Amu\Core\Events;

use Amu\Core\Models\WalletTransaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BalanceChanged
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public WalletTransaction $transaction)
    {
    }
}

<?php

namespace Tests\Feature\Wallet;

use Amu\Core\Contracts\WalletManager;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_wallet_transactions_update_balance_and_are_logged(): void
    {
        $user = User::factory()->create();
        $wallets = app(WalletManager::class);

        $account = $wallets->accountFor($user, 'chips');
        $wallets->credit($account, 500, ['reason' => 'seed']);
        $wallets->debit($account->fresh(), 125, ['reason' => 'buy-in']);

        $this->assertDatabaseHas('wallet_accounts', [
            'id' => $account->id,
            'balance' => 375,
        ]);

        $this->assertDatabaseCount('wallet_transactions', 2);
    }
}

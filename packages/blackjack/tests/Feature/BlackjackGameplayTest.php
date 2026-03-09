<?php

namespace Tests\Feature\Blackjack;

use Amu\Blackjack\Models\BlackjackRound;
use Amu\Blackjack\Models\BlackjackTable;
use Amu\Core\Contracts\ModuleRegistry;
use Amu\Core\Contracts\WalletManager;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlackjackGameplayTest extends TestCase
{
    use RefreshDatabase;

    private User $host;
    private User $player;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        app(ModuleRegistry::class);

        $this->host = User::factory()->create();
        $this->host->assignRole('host');

        $this->player = User::factory()->create();
        $this->player->assignRole('player');

        $wallets = app(WalletManager::class);
        $wallets->credit($wallets->accountFor($this->host), 5000, ['reason' => 'funding']);
        $wallets->credit($wallets->accountFor($this->player), 5000, ['reason' => 'funding']);
    }

    public function test_blackjack_room_can_be_opened_as_a_playable_table(): void
    {
        $roomId = $this->createBlackjackRoom();

        $this->actingAs($this->host, 'sanctum')
            ->postJson("/api/v1/blackjack/rooms/{$roomId}/open")
            ->assertCreated()
            ->assertJsonPath('data.room_id', $roomId)
            ->assertJsonPath('data.session_id', fn ($value) => $value > 0);
    }

    public function test_player_can_place_a_valid_bet_and_wallet_is_debited(): void
    {
        $round = $this->openRoundWithSeats();

        $this->actingAs($this->player, 'sanctum')
            ->postJson("/api/v1/blackjack/rounds/{$round->id}/bets", ['amount' => 200])
            ->assertOk()
            ->assertJsonPath('data.bets.0.amount', 200);

        $this->assertDatabaseHas('blackjack_bets', [
            'blackjack_round_id' => $round->id,
            'user_id' => $this->player->id,
            'amount' => 200,
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'reason' => 'blackjack-bet',
            'amount' => -200,
        ]);
    }

    public function test_hit_and_stand_flow_resolves_a_player_win(): void
    {
        $round = $this->openRoundWithSeatsAndBet();
        $this->setShoe($round->table, ['5S', '6H', '9C', '7D', '8S', '8C']);

        $this->actingAs($this->host, 'sanctum')
            ->postJson("/api/v1/blackjack/rounds/{$round->id}/deal")
            ->assertOk()
            ->assertJsonPath('data.current_turn_seat_id', fn ($value) => $value > 0);

        $this->actingAs($this->player, 'sanctum')
            ->postJson("/api/v1/blackjack/rounds/{$round->id}/hit")
            ->assertOk();

        $this->actingAs($this->player, 'sanctum')
            ->postJson("/api/v1/blackjack/rounds/{$round->id}/stand")
            ->assertOk();

        $resolved = BlackjackRound::query()->with(['hands', 'bets'])->findOrFail($round->id);

        $this->assertSame('settled', $resolved->status);
        $this->assertSame(24, $resolved->dealer_value);
        $this->assertSame('win', $resolved->hands->first()->outcome);
    }

    public function test_dealer_resolution_covers_blackjack_push_and_loss_outcomes(): void
    {
        $blackjackRound = $this->openRoundWithSeatsAndBet(100);
        $this->setShoe($blackjackRound->table, ['AS', 'KH', '9C', '7D']);
        $this->actingAs($this->host, 'sanctum')->postJson("/api/v1/blackjack/rounds/{$blackjackRound->id}/deal")->assertOk();
        $this->actingAs($this->host, 'sanctum')->postJson("/api/v1/blackjack/rounds/{$blackjackRound->id}/settle")->assertOk();

        $pushRound = $this->openRoundWithSeatsAndBet(100);
        $this->setShoe($pushRound->table, ['10S', '7H', '9C', '8D']);
        $this->actingAs($this->host, 'sanctum')->postJson("/api/v1/blackjack/rounds/{$pushRound->id}/deal")->assertOk();
        $this->actingAs($this->player, 'sanctum')->postJson("/api/v1/blackjack/rounds/{$pushRound->id}/stand")->assertOk();

        $lossRound = $this->openRoundWithSeatsAndBet(100);
        $this->setShoe($lossRound->table, ['10S', '6H', '9C', '8D']);
        $this->actingAs($this->host, 'sanctum')->postJson("/api/v1/blackjack/rounds/{$lossRound->id}/deal")->assertOk();
        $this->actingAs($this->player, 'sanctum')->postJson("/api/v1/blackjack/rounds/{$lossRound->id}/stand")->assertOk();

        $blackjackResolved = BlackjackRound::find($blackjackRound->id)->load('hands');
        $pushResolved = BlackjackRound::find($pushRound->id)->load('hands');
        $lossResolved = BlackjackRound::find($lossRound->id)->load('hands');

        $this->assertSame('blackjack', $blackjackResolved->hands->first()->outcome);
        $this->assertSame('push', $pushResolved->hands->first()->outcome);
        $this->assertSame('loss', $lossResolved->hands->first()->outcome);
    }

    public function test_settlement_is_idempotent_and_wallet_payout_is_only_created_once(): void
    {
        $round = $this->openRoundWithSeatsAndBet(100);
        $this->setShoe($round->table, ['AS', 'KH', '9C', '7D']);

        $this->actingAs($this->host, 'sanctum')->postJson("/api/v1/blackjack/rounds/{$round->id}/deal")->assertOk();

        $transactionCount = \Amu\Core\Models\WalletTransaction::query()->where('reason', 'blackjack-settlement')->count();

        $this->actingAs($this->host, 'sanctum')
            ->postJson("/api/v1/blackjack/rounds/{$round->id}/settle")
            ->assertOk();

        $this->assertSame(
            $transactionCount,
            \Amu\Core\Models\WalletTransaction::query()->where('reason', 'blackjack-settlement')->count()
        );
    }

    private function createBlackjackRoom(): int
    {
        $response = $this->actingAs($this->host, 'sanctum')->postJson('/api/v1/rooms', [
            'game_module_slug' => 'blackjack',
            'name' => 'Blackjack Table',
            'visibility' => 'public',
            'min_players' => 1,
            'max_players' => 5,
        ]);

        $response->assertCreated();

        return (int) $response->json('data.id');
    }

    private function openRoundWithSeats(): BlackjackRound
    {
        $roomId = $this->createBlackjackRoom();

        $open = $this->actingAs($this->host, 'sanctum')
            ->postJson("/api/v1/blackjack/rooms/{$roomId}/open");

        $tableId = (int) $open->json('data.id');

        $this->actingAs($this->player, 'sanctum')
            ->postJson("/api/v1/blackjack/tables/{$tableId}/seats")
            ->assertOk();

        $roundResponse = $this->actingAs($this->host, 'sanctum')
            ->postJson("/api/v1/blackjack/tables/{$tableId}/rounds");

        return BlackjackRound::query()->findOrFail((int) $roundResponse->json('data.id'));
    }

    private function openRoundWithSeatsAndBet(int $amount = 100): BlackjackRound
    {
        $round = $this->openRoundWithSeats();

        $this->actingAs($this->player, 'sanctum')
            ->postJson("/api/v1/blackjack/rounds/{$round->id}/bets", ['amount' => $amount])
            ->assertOk();

        return $round->fresh(['table', 'bets', 'hands']);
    }

    private function setShoe(BlackjackTable $table, array $cards): void
    {
        $table->forceFill(['shoe_state' => $cards])->save();
    }
}

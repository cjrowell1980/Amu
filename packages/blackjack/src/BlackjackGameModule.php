<?php

namespace Amu\Blackjack;

use Amu\Core\Contracts\GameModule;
use Amu\Core\Contracts\ModuleRegistry;

class BlackjackGameModule implements GameModule
{
    public function slug(): string
    {
        return 'blackjack';
    }

    public function name(): string
    {
        return 'Blackjack';
    }

    public function version(): string
    {
        return '0.1.0';
    }

    public function description(): ?string
    {
        return 'Playable MVP Blackjack with betting, dealer resolution, and wallet settlement.';
    }

    public function settings(): array
    {
        return [
            'decks' => config('blackjack.decks', 6),
            'dealer_hits_soft_17' => config('blackjack.dealer_hits_soft_17', false),
            'default_min_bet' => config('blackjack.default_min_bet', 100),
            'default_max_bet' => config('blackjack.default_max_bet', 10000),
        ];
    }

    public function boot(ModuleRegistry $registry): void
    {
    }
}

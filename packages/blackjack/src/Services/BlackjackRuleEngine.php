<?php

namespace Amu\Blackjack\Services;

class BlackjackRuleEngine
{
    public function createShoe(int $decks = 6): array
    {
        $cards = [];
        $ranks = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
        $suits = ['S', 'H', 'D', 'C'];

        for ($deck = 0; $deck < $decks; $deck++) {
            foreach ($suits as $suit) {
                foreach ($ranks as $rank) {
                    $cards[] = $rank.$suit;
                }
            }
        }

        shuffle($cards);

        return $cards;
    }

    public function draw(array &$shoe, int $count = 1): array
    {
        $drawn = [];

        for ($index = 0; $index < $count; $index++) {
            $drawn[] = array_shift($shoe);
        }

        return $drawn;
    }

    public function evaluate(array $cards): array
    {
        $value = 0;
        $aces = 0;

        foreach ($cards as $card) {
            $rank = substr($card, 0, -1);

            if ($rank === 'A') {
                $value += 11;
                $aces++;
                continue;
            }

            if (in_array($rank, ['K', 'Q', 'J'], true)) {
                $value += 10;
                continue;
            }

            $value += (int) $rank;
        }

        while ($value > 21 && $aces > 0) {
            $value -= 10;
            $aces--;
        }

        return [
            'value' => $value,
            'soft' => $aces > 0,
        ];
    }

    public function isBlackjack(array $cards): bool
    {
        return count($cards) === 2 && $this->evaluate($cards)['value'] === 21;
    }

    public function isBust(array $cards): bool
    {
        return $this->evaluate($cards)['value'] > 21;
    }

    public function dealerShouldHit(array $cards, bool $hitsSoft17): bool
    {
        $evaluation = $this->evaluate($cards);

        if ($evaluation['value'] < 17) {
            return true;
        }

        if ($hitsSoft17 && $evaluation['value'] === 17 && $evaluation['soft']) {
            return true;
        }

        return false;
    }
}

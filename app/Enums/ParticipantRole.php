<?php

namespace App\Enums;

enum ParticipantRole: string
{
    case Player    = 'player';
    case Spectator = 'spectator';

    public function label(): string
    {
        return match($this) {
            self::Player    => 'Player',
            self::Spectator => 'Spectator',
        };
    }
}

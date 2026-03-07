<?php

namespace App\Enums;

enum RoomMemberRole: string
{
    case Host      = 'host';
    case Player    = 'player';
    case Spectator = 'spectator';

    public function label(): string
    {
        return match($this) {
            self::Host      => 'Host',
            self::Player    => 'Player',
            self::Spectator => 'Spectator',
        };
    }

    public function canToggleReady(): bool
    {
        return match($this) {
            self::Host, self::Player => true,
            self::Spectator          => false,
        };
    }

    public function countsTowardCapacity(): bool
    {
        return match($this) {
            self::Host, self::Player => true,
            self::Spectator          => false,
        };
    }
}

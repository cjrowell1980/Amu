<?php

namespace App\Enums;

enum ConnectionStatus: string
{
    case Connected    = 'connected';
    case Disconnected = 'disconnected';
    case Reconnecting = 'reconnecting';

    public function label(): string
    {
        return match($this) {
            self::Connected    => 'Connected',
            self::Disconnected => 'Disconnected',
            self::Reconnecting => 'Reconnecting',
        };
    }
}

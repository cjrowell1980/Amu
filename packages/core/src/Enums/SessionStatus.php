<?php

namespace Amu\Core\Enums;

enum SessionStatus: string
{
    case Pending = 'pending';
    case Waiting = 'waiting';
    case Active = 'active';
    case Paused = 'paused';
    case Finished = 'finished';
    case Cancelled = 'cancelled';

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, match ($this) {
            self::Pending => [self::Waiting, self::Cancelled],
            self::Waiting => [self::Active, self::Cancelled],
            self::Active => [self::Paused, self::Finished, self::Cancelled],
            self::Paused => [self::Active, self::Finished, self::Cancelled],
            self::Finished, self::Cancelled => [],
        }, true);
    }
}

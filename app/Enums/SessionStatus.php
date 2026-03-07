<?php

namespace App\Enums;

enum SessionStatus: string
{
    case Pending   = 'pending';
    case Created   = 'created';
    case Starting  = 'starting';
    case Active    = 'active';
    case Paused    = 'paused';
    case Completed = 'completed';
    case Abandoned = 'abandoned';
    case Cancelled = 'cancelled';

    /**
     * Valid transitions FROM this status.
     */
    public function allowedTransitions(): array
    {
        return match($this) {
            self::Pending   => [self::Created, self::Cancelled],
            self::Created   => [self::Starting, self::Cancelled],
            self::Starting  => [self::Active, self::Cancelled],
            self::Active    => [self::Paused, self::Completed, self::Abandoned, self::Cancelled],
            self::Paused    => [self::Active, self::Abandoned, self::Cancelled],
            self::Completed => [],
            self::Abandoned => [],
            self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), strict: true);
    }

    public function isTerminal(): bool
    {
        return match($this) {
            self::Completed, self::Abandoned, self::Cancelled => true,
            default => false,
        };
    }

    public function isActive(): bool
    {
        return match($this) {
            self::Active, self::Paused => true,
            default => false,
        };
    }

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'Pending',
            self::Created   => 'Created',
            self::Starting  => 'Starting',
            self::Active    => 'Active',
            self::Paused    => 'Paused',
            self::Completed => 'Completed',
            self::Abandoned => 'Abandoned',
            self::Cancelled => 'Cancelled',
        };
    }
}

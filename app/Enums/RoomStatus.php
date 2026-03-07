<?php

namespace App\Enums;

enum RoomStatus: string
{
    case Waiting    = 'waiting';
    case Ready      = 'ready';
    case Starting   = 'starting';
    case InProgress = 'in_progress';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';
    case Closed     = 'closed';

    /**
     * Valid transitions FROM this status.
     * Returns the set of statuses this status may transition to.
     */
    public function allowedTransitions(): array
    {
        return match($this) {
            self::Waiting    => [self::Ready, self::Cancelled, self::Closed],
            self::Ready      => [self::Waiting, self::Starting, self::Cancelled, self::Closed],
            self::Starting   => [self::InProgress, self::Cancelled],
            self::InProgress => [self::Completed, self::Cancelled],
            self::Completed  => [],
            self::Cancelled  => [],
            self::Closed     => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), strict: true);
    }

    /** Whether the room can currently accept new members. */
    public function acceptsNewMembers(): bool
    {
        return match($this) {
            self::Waiting, self::Ready => true,
            default                    => false,
        };
    }

    /** Whether the room is in a terminal (non-modifiable) state. */
    public function isTerminal(): bool
    {
        return match($this) {
            self::Completed, self::Cancelled, self::Closed => true,
            default => false,
        };
    }

    /** Human-readable label. */
    public function label(): string
    {
        return match($this) {
            self::Waiting    => 'Waiting',
            self::Ready      => 'Ready',
            self::Starting   => 'Starting',
            self::InProgress => 'In Progress',
            self::Completed  => 'Completed',
            self::Cancelled  => 'Cancelled',
            self::Closed     => 'Closed',
        };
    }
}

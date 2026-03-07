<?php

namespace App\Enums;

enum RoomVisibility: string
{
    case Public   = 'public';
    case Private  = 'private';
    case Unlisted = 'unlisted';

    public function label(): string
    {
        return match($this) {
            self::Public   => 'Public',
            self::Private  => 'Private',
            self::Unlisted => 'Unlisted',
        };
    }

    /** Whether the room appears in public lobby listings. */
    public function appearsInListing(): bool
    {
        return $this === self::Public;
    }

    /** Whether a password is required to join. */
    public function requiresPassword(): bool
    {
        return $this === self::Private;
    }
}

<?php

namespace App\Enums;

/**
 * Controls how a game is exposed to players via the registry.
 *
 * enabled  - visible and joinable by all authenticated players
 * beta     - visible and joinable only by users with the 'beta_tester' role/permission
 * hidden   - only visible to operators/admins; used for internal testing
 * disabled - not visible or joinable by anyone (operator can still see it in admin panel)
 */
enum GameAvailability: string
{
    case Enabled  = 'enabled';
    case Beta     = 'beta';
    case Hidden   = 'hidden';
    case Disabled = 'disabled';

    public function label(): string
    {
        return match($this) {
            self::Enabled  => 'Enabled',
            self::Beta     => 'Beta',
            self::Hidden   => 'Hidden',
            self::Disabled => 'Disabled',
        };
    }

    public function isAvailableToPlayers(): bool
    {
        return $this === self::Enabled;
    }

    public function isAvailableToBeta(): bool
    {
        return match($this) {
            self::Enabled, self::Beta => true,
            default => false,
        };
    }

    public function isVisibleToOperators(): bool
    {
        return true; // operators can always see all games in admin panel
    }
}

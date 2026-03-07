<?php

namespace App\Models;

use App\Enums\GameAvailability;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'module_class',
        'availability',
        'supports_teams',
        'min_players',
        'max_players',
        'default_config',
        'version',
        'thumbnail_url',
        'play_count',
        'required_role',
    ];

    protected function casts(): array
    {
        return [
            'availability'   => GameAvailability::class,
            'supports_teams' => 'boolean',
            'min_players'    => 'integer',
            'max_players'    => 'integer',
            'play_count'     => 'integer',
            'default_config' => 'array',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function rooms(): HasMany
    {
        return $this->hasMany(GameRoom::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** Visible and joinable by all authenticated players. */
    public function scopeEnabled($query)
    {
        return $query->where('availability', GameAvailability::Enabled->value);
    }

    /** Enabled + beta (for beta testers). */
    public function scopeAvailableForBeta($query)
    {
        return $query->whereIn('availability', [
            GameAvailability::Enabled->value,
            GameAvailability::Beta->value,
        ]);
    }

    /** Everything except disabled (for operators). */
    public function scopeVisibleToOperators($query)
    {
        return $query->whereNot('availability', GameAvailability::Disabled->value);
    }

    // -------------------------------------------------------------------------
    // Domain helpers
    // -------------------------------------------------------------------------

    public function isEnabled(): bool
    {
        return $this->availability === GameAvailability::Enabled;
    }

    public function isBeta(): bool
    {
        return $this->availability === GameAvailability::Beta;
    }

    public function isHidden(): bool
    {
        return $this->availability === GameAvailability::Hidden;
    }

    public function isDisabled(): bool
    {
        return $this->availability === GameAvailability::Disabled;
    }

    /**
     * Check whether the game has a valid, resolvable module class.
     */
    public function hasValidModule(): bool
    {
        return ! empty($this->module_class) && class_exists($this->module_class);
    }

    /**
     * Increment play_count atomically.
     */
    public function incrementPlayCount(): void
    {
        $this->increment('play_count');
    }
}

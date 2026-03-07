<?php

namespace App\Models;

use App\Enums\SessionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class GameSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'game_id',
        'game_room_id',
        'status',
        'session_config',
        'result_summary',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'status'         => SessionStatus::class,
            'session_config' => 'array',
            'result_summary' => 'array',
            'started_at'     => 'datetime',
            'ended_at'       => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (GameSession $session) {
            if (empty($session->uuid)) {
                $session->uuid = (string) Str::uuid();
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(GameRoom::class, 'game_room_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(GameSessionParticipant::class);
    }

    public function activePlayers(): HasMany
    {
        return $this->participants()->where('role', 'player')->whereNull('left_at');
    }

    // -------------------------------------------------------------------------
    // Domain helpers
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isTerminal(): bool
    {
        return $this->status->isTerminal();
    }

    public function canTransitionTo(SessionStatus $next): bool
    {
        return $this->status->canTransitionTo($next);
    }

    /**
     * Route model binding via UUID instead of numeric id.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

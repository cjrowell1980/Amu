<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GameSession extends Model
{
    use SoftDeletes;

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
            'session_config' => 'array',
            'result_summary' => 'array',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (GameSession $session) {
            if (empty($session->uuid)) {
                $session->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

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

    public function isActive(): bool
    {
        return in_array($this->status, ['created', 'starting', 'active', 'paused']);
    }
}

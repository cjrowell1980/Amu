<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class GameRoom extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'game_id',
        'host_user_id',
        'visibility',
        'status',
        'password_hash',
        'max_players',
        'allow_spectators',
        'room_config',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'allow_spectators' => 'boolean',
            'max_players' => 'integer',
            'room_config' => 'array',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(GameRoomMember::class);
    }

    public function activeMembers(): HasMany
    {
        return $this->hasMany(GameRoomMember::class)->whereNull('left_at');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }

    public function activeSession(): HasOne
    {
        return $this->hasOne(GameSession::class)->whereIn('status', ['created', 'starting', 'active', 'paused']);
    }

    public function scopePubliclyVisible($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function hasPassword(): bool
    {
        return ! is_null($this->password_hash);
    }

    public function effectiveMaxPlayers(): int
    {
        return $this->max_players ?? $this->game->max_players;
    }

    public function isFull(): bool
    {
        return $this->activeMembers()->count() >= $this->effectiveMaxPlayers();
    }
}

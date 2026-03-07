<?php

namespace App\Models;

use App\Enums\RoomMemberRole;
use App\Enums\RoomStatus;
use App\Enums\RoomVisibility;
use App\Enums\SessionStatus;
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
            'status'           => RoomStatus::class,
            'visibility'       => RoomVisibility::class,
            'allow_spectators' => 'boolean',
            'max_players'      => 'integer',
            'room_config'      => 'array',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

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

    public function activePlayers(): HasMany
    {
        return $this->activeMembers()->whereIn('role', [
            RoomMemberRole::Host->value,
            RoomMemberRole::Player->value,
        ]);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }

    public function activeSession(): HasOne
    {
        $activeStatuses = collect(SessionStatus::cases())
            ->filter(fn (SessionStatus $s) => ! $s->isTerminal())
            ->map(fn (SessionStatus $s) => $s->value)
            ->values()
            ->all();

        return $this->hasOne(GameSession::class)->whereIn('status', $activeStatuses);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopePubliclyVisible($query)
    {
        return $query->where('visibility', RoomVisibility::Public->value);
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', RoomStatus::Waiting->value);
    }

    public function scopeOpenForJoining($query)
    {
        return $query->whereIn('status', [
            RoomStatus::Waiting->value,
            RoomStatus::Ready->value,
        ]);
    }

    // -------------------------------------------------------------------------
    // Domain helpers
    // -------------------------------------------------------------------------

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
        return $this->activePlayers()->count() >= $this->effectiveMaxPlayers();
    }

    public function isOpenForJoining(): bool
    {
        return $this->status->acceptsNewMembers() && ! $this->isFull();
    }

    public function canTransitionTo(RoomStatus $next): bool
    {
        return $this->status->canTransitionTo($next);
    }

    /**
     * Check whether all active (non-spectator) members are ready.
     * The host is excluded from the ready check only if there are other players;
     * otherwise at least 1 non-host player must be present.
     */
    public function allPlayersReady(): bool
    {
        $players = $this->activePlayers()->get();

        if ($players->count() < ($this->game->min_players ?? 1)) {
            return false;
        }

        return $players->every(fn (GameRoomMember $m) => $m->is_ready);
    }

    /**
     * Return the member record for a given user, or null.
     */
    public function memberFor(User $user): ?GameRoomMember
    {
        return $this->activeMembers()->where('user_id', $user->id)->first();
    }

    public function hasMember(User $user): bool
    {
        return $this->memberFor($user) !== null;
    }
}

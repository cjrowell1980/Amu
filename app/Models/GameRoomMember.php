<?php

namespace App\Models;

use App\Enums\RoomMemberRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameRoomMember extends Model
{
    protected $fillable = [
        'game_room_id',
        'user_id',
        'role',
        'is_ready',
        'team_number',
        'seat_number',
        'joined_at',
        'left_at',
    ];

    protected function casts(): array
    {
        return [
            'role'        => RoomMemberRole::class,
            'is_ready'    => 'boolean',
            'team_number' => 'integer',
            'seat_number' => 'integer',
            'joined_at'   => 'datetime',
            'left_at'     => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(GameRoom::class, 'game_room_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return is_null($this->left_at);
    }

    public function isHost(): bool
    {
        return $this->role === RoomMemberRole::Host;
    }

    public function isSpectator(): bool
    {
        return $this->role === RoomMemberRole::Spectator;
    }

    public function canToggleReady(): bool
    {
        return $this->role->canToggleReady();
    }

    public function countsTowardCapacity(): bool
    {
        return $this->role->countsTowardCapacity();
    }
}

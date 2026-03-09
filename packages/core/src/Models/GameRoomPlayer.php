<?php

namespace Amu\Core\Models;

use Amu\Core\Enums\ParticipantConnection;
use Amu\Core\Enums\ParticipantRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameRoomPlayer extends Model
{
    protected $fillable = [
        'game_room_id',
        'user_id',
        'participation',
        'connection_status',
        'seat_number',
        'is_ready',
        'joined_at',
        'left_at',
    ];

    protected function casts(): array
    {
        return [
            'participation' => ParticipantRole::class,
            'connection_status' => ParticipantConnection::class,
            'is_ready' => 'boolean',
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
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
}

<?php

namespace App\Models;

use App\Enums\ConnectionStatus;
use App\Enums\ParticipantRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameSessionParticipant extends Model
{
    protected $fillable = [
        'game_session_id',
        'user_id',
        'role',
        'team_number',
        'seat_number',
        'connection_status',
        'last_seen_at',
        'disconnected_at',
        'reconnect_token',
        'final_rank',
        'score',
        'result_detail',
        'joined_at',
        'left_at',
    ];

    protected function casts(): array
    {
        return [
            'role'              => ParticipantRole::class,
            'connection_status' => ConnectionStatus::class,
            'team_number'       => 'integer',
            'seat_number'       => 'integer',
            'final_rank'        => 'integer',
            'score'             => 'integer',
            'result_detail'     => 'array',
            'joined_at'         => 'datetime',
            'left_at'           => 'datetime',
            'last_seen_at'      => 'datetime',
            'disconnected_at'   => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'game_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -------------------------------------------------------------------------
    // Domain helpers
    // -------------------------------------------------------------------------

    public function isConnected(): bool
    {
        return $this->connection_status === ConnectionStatus::Connected;
    }

    public function isDisconnected(): bool
    {
        return $this->connection_status === ConnectionStatus::Disconnected;
    }

    public function isPlayer(): bool
    {
        return $this->role === ParticipantRole::Player;
    }

    public function isSpectator(): bool
    {
        return $this->role === ParticipantRole::Spectator;
    }
}

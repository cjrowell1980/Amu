<?php

namespace App\Models;

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
        'final_rank',
        'score',
        'result_detail',
        'joined_at',
        'left_at',
    ];

    protected function casts(): array
    {
        return [
            'team_number' => 'integer',
            'seat_number' => 'integer',
            'final_rank' => 'integer',
            'score' => 'integer',
            'result_detail' => 'array',
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'game_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

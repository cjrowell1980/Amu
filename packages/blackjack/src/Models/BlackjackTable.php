<?php

namespace Amu\Blackjack\Models;

use Amu\Core\Models\GameRoom;
use Amu\Core\Models\GameSession;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlackjackTable extends Model
{
    protected $fillable = [
        'game_room_id',
        'game_session_id',
        'shoe_state',
        'decks',
        'dealer_hits_soft_17',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'shoe_state' => 'array',
            'dealer_hits_soft_17' => 'boolean',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(GameRoom::class, 'game_room_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'game_session_id');
    }

    public function seats(): HasMany
    {
        return $this->hasMany(BlackjackSeat::class)->where('status', 'active')->orderBy('seat_number');
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(BlackjackRound::class)->latest('id');
    }
}

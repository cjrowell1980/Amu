<?php

namespace Amu\Blackjack\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlackjackRound extends Model
{
    protected $fillable = [
        'blackjack_table_id',
        'status',
        'current_turn_seat_id',
        'dealer_cards',
        'dealer_value',
        'outcome_summary',
        'settled_at',
    ];

    protected function casts(): array
    {
        return [
            'dealer_cards' => 'array',
            'outcome_summary' => 'array',
            'settled_at' => 'datetime',
        ];
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(BlackjackTable::class, 'blackjack_table_id');
    }

    public function currentTurnSeat(): BelongsTo
    {
        return $this->belongsTo(BlackjackSeat::class, 'current_turn_seat_id');
    }

    public function hands(): HasMany
    {
        return $this->hasMany(BlackjackHand::class)->orderBy('id');
    }

    public function bets(): HasMany
    {
        return $this->hasMany(BlackjackBet::class)->orderBy('id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(BlackjackAction::class)->orderBy('id');
    }
}

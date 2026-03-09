<?php

namespace Amu\Blackjack\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlackjackHand extends Model
{
    protected $fillable = [
        'blackjack_round_id',
        'user_id',
        'blackjack_seat_id',
        'cards',
        'value',
        'status',
        'is_blackjack',
        'is_bust',
        'outcome',
        'payout',
    ];

    protected function casts(): array
    {
        return [
            'cards' => 'array',
            'is_blackjack' => 'boolean',
            'is_bust' => 'boolean',
            'payout' => 'integer',
        ];
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(BlackjackRound::class, 'blackjack_round_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seat(): BelongsTo
    {
        return $this->belongsTo(BlackjackSeat::class, 'blackjack_seat_id');
    }
}

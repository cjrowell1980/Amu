<?php

namespace Amu\Blackjack\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlackjackBet extends Model
{
    protected $fillable = [
        'blackjack_round_id',
        'user_id',
        'amount',
        'status',
        'payout',
        'settled_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'payout' => 'integer',
            'settled_at' => 'datetime',
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
}

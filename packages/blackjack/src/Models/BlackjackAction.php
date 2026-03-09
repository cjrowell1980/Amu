<?php

namespace Amu\Blackjack\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlackjackAction extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'blackjack_round_id',
        'user_id',
        'action',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
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

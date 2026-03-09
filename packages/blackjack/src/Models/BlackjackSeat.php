<?php

namespace Amu\Blackjack\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlackjackSeat extends Model
{
    protected $fillable = [
        'blackjack_table_id',
        'user_id',
        'seat_number',
        'status',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(BlackjackTable::class, 'blackjack_table_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace Amu\Core\Models;

use Amu\Core\Enums\RoomStatus;
use Amu\Core\Enums\RoomVisibility;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameRoom extends Model
{
    protected $fillable = [
        'game_module_id',
        'host_user_id',
        'code',
        'name',
        'visibility',
        'status',
        'min_players',
        'max_players',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'visibility' => RoomVisibility::class,
            'status' => RoomStatus::class,
            'settings' => 'array',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(GameModule::class, 'game_module_id');
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(GameRoomPlayer::class)->whereNull('left_at');
    }
}

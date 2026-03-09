<?php

namespace Amu\Core\Models;

use Amu\Core\Enums\SessionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GameSession extends Model
{
    protected $fillable = [
        'game_module_id',
        'game_room_id',
        'uuid',
        'status',
        'settings',
        'started_at',
        'ended_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $session): void {
            $session->uuid ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'status' => SessionStatus::class,
            'settings' => 'array',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(GameModule::class, 'game_module_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(GameRoom::class, 'game_room_id');
    }
}

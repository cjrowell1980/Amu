<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'module_class',
        'enabled',
        'supports_teams',
        'min_players',
        'max_players',
        'default_config',
        'version',
        'thumbnail_url',
        'play_count',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'supports_teams' => 'boolean',
            'min_players' => 'integer',
            'max_players' => 'integer',
            'play_count' => 'integer',
            'default_config' => 'array',
        ];
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(GameRoom::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }
}

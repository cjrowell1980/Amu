<?php

namespace Amu\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameModule extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'package_name',
        'provider_class',
        'version',
        'enabled',
        'description',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(GameRoom::class);
    }
}

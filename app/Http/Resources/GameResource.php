<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'enabled' => $this->enabled,
            'supports_teams' => $this->supports_teams,
            'min_players' => $this->min_players,
            'max_players' => $this->max_players,
            'thumbnail_url' => $this->thumbnail_url,
            'play_count' => $this->play_count,
        ];
    }
}

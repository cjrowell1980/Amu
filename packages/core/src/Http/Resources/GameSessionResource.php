<?php

namespace Amu\Core\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'status' => $this->status->value,
            'room_id' => $this->game_room_id,
            'module_slug' => $this->module->slug,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
        ];
    }
}

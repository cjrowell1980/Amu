<?php

namespace Amu\Core\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameRoomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'status' => $this->status->value,
            'visibility' => $this->visibility->value,
            'min_players' => $this->min_players,
            'max_players' => $this->max_players,
            'module' => [
                'slug' => $this->module->slug,
                'name' => $this->module->name,
            ],
            'participants' => $this->players->map(fn ($player) => [
                'user_id' => $player->user_id,
                'participation' => $player->participation->value,
                'connection_status' => $player->connection_status->value,
                'is_ready' => $player->is_ready,
            ])->values(),
        ];
    }
}

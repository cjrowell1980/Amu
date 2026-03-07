<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameRoomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'code'             => $this->code,
            'name'             => $this->name,
            'status'           => $this->status->value,
            'visibility'       => $this->visibility->value,
            'has_password'     => $this->hasPassword(),
            'allow_spectators' => $this->allow_spectators,
            'max_players'      => $this->effectiveMaxPlayers(),
            'member_count'     => $this->whenCounted('activeMembers', fn () => $this->active_members_count),
            'is_full'          => $this->isFull(),
            'game'             => $this->whenLoaded('game', fn () => new GameResource($this->game)),
            'host'             => $this->whenLoaded('host', fn () => [
                'id'           => $this->host->id,
                'name'         => $this->host->name,
                'display_name' => $this->host->profile?->display_name,
            ]),
            'members'          => $this->whenLoaded('activeMembers', fn () =>
                GameRoomMemberResource::collection($this->activeMembers)
            ),
            'active_session'   => $this->whenLoaded('activeSession', fn () =>
                $this->activeSession ? new GameSessionResource($this->activeSession) : null
            ),
            'created_at'       => $this->created_at->toIso8601String(),
        ];
    }
}

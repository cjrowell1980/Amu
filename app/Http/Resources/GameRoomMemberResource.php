<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameRoomMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'role' => $this->role,
            'is_ready' => $this->is_ready,
            'team_number' => $this->team_number,
            'seat_number' => $this->seat_number,
            'joined_at' => $this->joined_at->toIso8601String(),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'display_name' => $this->user->profile?->display_name,
                'avatar_url' => $this->user->profile?->avatar_url,
            ]),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'              => $this->uuid,
            'status'            => $this->status->value,
            'game'              => $this->whenLoaded('game', fn () => new GameResource($this->game)),
            'room'              => $this->whenLoaded('room', fn () => [
                'id'   => $this->room->id,
                'code' => $this->room->code,
                'name' => $this->room->name,
            ]),
            'participants'      => $this->whenLoaded('participants', fn () =>
                GameSessionParticipantResource::collection($this->participants)
            ),
            'participant_count' => $this->whenCounted('participants'),
            'session_config'    => $this->when(
                $request->user()?->hasRole(['admin', 'operator']),
                $this->session_config,
            ),
            'result_summary'    => $this->when(
                $this->status->isTerminal(),
                $this->result_summary,
            ),
            'started_at'        => $this->started_at?->toIso8601String(),
            'ended_at'          => $this->ended_at?->toIso8601String(),
            'created_at'        => $this->created_at->toIso8601String(),
        ];
    }
}

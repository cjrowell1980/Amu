<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameSessionParticipantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isSelf     = $request->user()?->id === $this->user_id;
        $isOperator = $request->user()?->hasRole(['admin', 'operator', 'moderator']);

        return [
            'user_id'           => $this->user_id,
            'display_name'      => $this->user?->profile?->display_name ?? $this->user?->name,
            'role'              => $this->role->value,
            'team_number'       => $this->team_number,
            'seat_number'       => $this->seat_number,
            'connection_status' => $this->connection_status->value,
            'last_seen_at'      => $this->last_seen_at?->toIso8601String(),

            // Scores are shown only after session is terminal, or to operators
            'final_rank'        => $this->when(
                $isOperator || $this->session?->status?->isTerminal(),
                $this->final_rank,
            ),
            'score'             => $this->when(
                $isOperator || $this->session?->status?->isTerminal(),
                $this->score,
            ),

            // Result detail only to the player themselves, after session ends
            'result_detail'     => $this->when(
                ($isSelf && $this->session?->status?->isTerminal()) || $isOperator,
                $this->result_detail,
            ),

            'joined_at'         => $this->joined_at?->toIso8601String(),
        ];
    }
}

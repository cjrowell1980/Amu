<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isOperator = $user && $user->hasRole(['admin', 'operator']);

        return [
            'id'             => $this->id,
            'slug'           => $this->slug,
            'name'           => $this->name,
            'description'    => $this->description,
            'availability'   => $this->availability->value,
            'supports_teams' => $this->supports_teams,
            'min_players'    => $this->min_players,
            'max_players'    => $this->max_players,
            'thumbnail_url'  => $this->thumbnail_url,
            'play_count'     => $this->play_count,
            'version'        => $this->version,

            // Operator-only fields
            'module_class'   => $this->when($isOperator, $this->module_class),
            'required_role'  => $this->when($isOperator, $this->required_role),
            'has_module'     => $this->when($isOperator, $this->hasValidModule()),
        ];
    }
}

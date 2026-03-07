<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'display_name' => $this->display_name,
            'avatar_url' => $this->avatar_url,
            'status' => $this->status,
            'country_code' => $this->country_code,
        ];
    }
}

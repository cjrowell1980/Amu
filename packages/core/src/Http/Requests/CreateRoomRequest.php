<?php

namespace Amu\Core\Http\Requests;

use Amu\Core\Enums\RoomStatus;
use Amu\Core\Enums\RoomVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'game_module_slug' => ['required', 'string', 'exists:game_modules,slug'],
            'name' => ['required', 'string', 'max:120'],
            'visibility' => ['required', new Enum(RoomVisibility::class)],
            'status' => ['nullable', new Enum(RoomStatus::class)],
            'min_players' => ['required', 'integer', 'min:1', 'max:100'],
            'max_players' => ['required', 'integer', 'gte:min_players', 'max:100'],
        ];
    }
}

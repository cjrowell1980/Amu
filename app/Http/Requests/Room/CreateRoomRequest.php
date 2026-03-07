<?php

namespace App\Http\Requests\Room;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:120'],
            'game_id' => ['required', 'integer', 'exists:games,id'],
            'visibility' => ['nullable', Rule::in(['public', 'private', 'unlisted'])],
            'password' => ['nullable', 'string', 'min:4', 'max:100'],
            'max_players' => ['nullable', 'integer', 'min:2', 'max:100'],
            'allow_spectators' => ['nullable', 'boolean'],
            'room_config' => ['nullable', 'array'],
        ];
    }
}

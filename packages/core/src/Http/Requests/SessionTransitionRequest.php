<?php

namespace Amu\Core\Http\Requests;

use Amu\Core\Enums\SessionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class SessionTransitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(SessionStatus::class)],
        ];
    }
}

<?php

namespace Amu\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReadyStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'is_ready' => ['required', 'boolean'],
        ];
    }
}

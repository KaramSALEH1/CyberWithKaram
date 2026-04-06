<?php

namespace App\Http\Requests\CommandCenter;

use Illuminate\Foundation\Http\FormRequest;

class CancelAgentCommandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:255'],
        ];
    }
}

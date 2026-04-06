<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AgentResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'command_uuid' => ['required', 'uuid'],
            'nonce' => ['required', 'string', 'max:255'],
            'result_status' => ['required', 'in:succeeded,failed'],
            'exit_code' => ['nullable', 'integer'],
            'duration_ms' => ['nullable', 'integer', 'min:0'],
            'stdout' => ['nullable', 'string'],
            'stderr' => ['nullable', 'string'],
            'artifacts' => ['nullable', 'array'],
        ];
    }
}

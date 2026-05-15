<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AgentHeartbeatApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'exists:services,id'],
            'ip_address' => ['nullable', 'ip'],
        ];
    }
}

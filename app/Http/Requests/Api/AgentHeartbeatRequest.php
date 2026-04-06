<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AgentHeartbeatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'in:online,offline,executing,degraded'],
            'ip_address' => ['nullable', 'ip'],
            'os_type' => ['nullable', 'string', 'max:64'],
            'agent_version' => ['nullable', 'string', 'max:32'],
            'host_fingerprint' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}

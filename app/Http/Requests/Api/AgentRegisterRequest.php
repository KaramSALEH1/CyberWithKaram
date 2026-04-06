<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AgentRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'agent_key' => ['required', 'string', 'max:128', 'unique:agents,agent_key'],
            'device_name' => ['required', 'string', 'max:255'],
            'ip_address' => ['nullable', 'ip'],
            'os_type' => ['nullable', 'string', 'max:64'],
            'agent_version' => ['nullable', 'string', 'max:32'],
            'host_fingerprint' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}

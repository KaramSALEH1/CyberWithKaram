<?php

namespace App\Http\Requests\CommandCenter;

use Illuminate\Foundation\Http\FormRequest;

class StoreAgentCommandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'agent_id' => ['required', 'exists:agents,id'],
            'command_key' => ['required', 'string', 'max:120', 'in:collect_inventory,run_health_check,sync_course_unlocks'],
            'payload' => ['nullable', 'array'],
            'ttl_seconds' => ['nullable', 'integer', 'min:30', 'max:3600'],
        ];
    }
}

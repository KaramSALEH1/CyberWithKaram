<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        $service = $this->route('service');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('services', 'slug')->ignore($service?->id)],
            'category' => ['required', 'string', 'max:120'],
            'icon' => ['nullable', 'string', 'max:50'],
            'logo_url' => ['nullable', 'url', 'max:2048'],
            'description' => ['required', 'string'],
            'full_description' => ['nullable', 'string'],
            'script_code' => ['nullable', 'string'],
            'is_automated' => ['nullable', 'boolean'],
            'is_visible' => ['nullable', 'boolean'],
        ];
    }
}

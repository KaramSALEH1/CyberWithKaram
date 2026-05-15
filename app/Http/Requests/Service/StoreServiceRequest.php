<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:services,slug'],
            'category' => ['required', 'string', 'max:120'],
            'icon' => ['nullable', 'string', 'max:50'],
            'logo_url' => ['nullable', 'url', 'max:2048'],
            'description' => ['required', 'string'],
            'full_description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'script_code' => ['nullable', 'string'],
            'payment_instructions' => ['nullable', 'string'],
            'is_automated' => ['nullable', 'boolean'],
            'is_visible' => ['nullable', 'boolean'],
            'is_available' => ['nullable', 'boolean'],
        ];
    }
}

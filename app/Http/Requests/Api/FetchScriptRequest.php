<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class FetchScriptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'exists:services,id'],
            'license_key' => ['required', 'string', 'max:64'],
        ];
    }
}

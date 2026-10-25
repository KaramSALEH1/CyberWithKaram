<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class EditServiceScriptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'script_file' => ['nullable', 'file', 'mimes:py,text/x-python,application/x-python,text/plain,application/octet-stream', 'max:10240'], // 10 MB
            'script_content' => ['nullable', 'string'],
        ];
    }

    public function getScriptFile(): ?UploadedFile
    {
        return $this->validated('script_file') ?? null;
    }

    public function getScriptContent(): ?string
    {
        return $this->validated('script_content') ?? null;
    }
}

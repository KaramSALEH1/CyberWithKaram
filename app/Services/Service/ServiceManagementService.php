<?php

namespace App\Services\Service;

use App\Models\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ServiceManagementService
{
    public function create(array $data): Service
    {
        return Service::create($this->normalize($data));
    }

    public function update(Service $service, array $data): Service
    {
        $service->update($this->normalize($data, $service));
        return $service->refresh();
    }

    private function normalize(array $data, ?Service $service = null): array
    {
        $title = Arr::get($data, 'title', $service?->title);
        $slug = Arr::get($data, 'slug');

        return [
            'title' => $title,
            'slug' => $slug ?: Str::slug((string) $title),
            'category' => Arr::get($data, 'category'),
            'description' => Arr::get($data, 'description'),
            'full_description' => Arr::get($data, 'full_description'),
            'icon' => Arr::get($data, 'icon', '🛡️'),
            'logo_url' => Arr::get($data, 'logo_url'),
            'script_code' => Arr::get($data, 'script_code'),
            'is_automated' => (bool) Arr::get($data, 'is_automated', false),
            'is_visible' => (bool) Arr::get($data, 'is_visible', false),
        ];
    }
}

<?php

namespace App\Services\Service;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ServiceScriptService
{
    private const STORAGE_FOLDER = 'service_scripts';
    private const FILENAME = 'script.py';

    /**
     * Store (or replace) the Python script for a service in a secure, private
     * storage folder: storage/app/service_scripts/{serviceId}/script.py
     *
     * The file is stored on the default `local` disk which is NOT publicly
     * accessible via URL. It is only served through the authenticated API
     * endpoint (AgentApiController@fetchScript).
     *
     * Accepts either an uploaded file or raw script content.
     */
    public function store(int $serviceId, UploadedFile $file = null, ?string $content = null): string
    {
        $folder = self::STORAGE_FOLDER . '/' . $serviceId;
        $path = "{$folder}/" . self::FILENAME;

        // Remove any previous script file for this service.
        $this->delete($serviceId);

        if ($file instanceof UploadedFile) {
            $this->validateUploadedFile($file);
            Storage::disk('local')->putFileAs($folder, $file, self::FILENAME);
        } elseif (is_string($content)) {
            Storage::disk('local')->put($path, $content);
        } else {
            throw ValidationException::withMessages([
                'script' => 'Provide either a Python file or script content.',
            ]);
        }

        return $path;
    }

    /**
     * Delete the stored script file for a service, if it exists.
     */
    public function delete(int $serviceId): void
    {
        $folder = self::STORAGE_FOLDER . '/' . $serviceId;
        $path = "{$folder}/" . self::FILENAME;

        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }

        // Remove the folder if empty.
        if (Storage::disk('local')->exists($folder)) {
            $files = Storage::disk('local')->allFiles($folder);
            if (empty($files)) {
                Storage::disk('local')->deleteDirectory($folder);
            }
        }
    }

    /**
     * Read the stored script content for a service.
     */
    public function get(int $serviceId): ?string
    {
        $path = self::STORAGE_FOLDER . "/{$serviceId}/" . self::FILENAME;

        if (! Storage::disk('local')->exists($path)) {
            return null;
        }

        return Storage::disk('local')->get($path);
    }

    private function validateUploadedFile(UploadedFile $file): void
    {
        $originalName = basename($file->getClientOriginalName());
        if (str_contains($originalName, '..') || str_contains($originalName, '/') || str_contains($originalName, '\\')) {
            throw ValidationException::withMessages([
                'script_file' => 'Invalid filename.',
            ]);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if ($extension !== 'py') {
            throw ValidationException::withMessages([
                'script_file' => 'Only Python (.py) files are allowed.',
            ]);
        }

        $mime = $file->getMimeType();
        $allowedMimes = ['text/x-python', 'application/x-python', 'text/plain', 'application/octet-stream'];
        if ($mime && ! in_array($mime, $allowedMimes, true)) {
            throw ValidationException::withMessages([
                'script_file' => 'Invalid Python file type.',
            ]);
        }
    }
}

<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class SecureVideoUpload
{
    private const ALLOWED_EXTENSIONS = ['mp4', 'mov', 'avi', 'wmv'];

    private const ALLOWED_MIMES = [
        'video/mp4',
        'video/quicktime',
        'video/x-msvideo',
        'video/x-ms-wmv',
        'application/octet-stream',
    ];

    public static function store(UploadedFile $file): string
    {
        $originalName = basename($file->getClientOriginalName());
        if (str_contains($originalName, '..') || str_contains($originalName, '/') || str_contains($originalName, '\\')) {
            throw ValidationException::withMessages([
                'video_file' => 'Invalid filename.',
            ]);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                'video_file' => 'Video must be mp4, mov, avi, or wmv.',
            ]);
        }

        $mime = $file->getMimeType();
        if ($mime && ! in_array($mime, self::ALLOWED_MIMES, true)) {
            throw ValidationException::withMessages([
                'video_file' => 'Invalid video file type.',
            ]);
        }

        $filename = hash('sha256', uniqid('', true) . $originalName) . '.' . $extension;

        return $file->storeAs('videos', $filename, 'public');
    }
}

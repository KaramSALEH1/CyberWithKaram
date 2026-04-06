<?php

namespace App\Services\CommandCenter;

class CommandSigningService
{
    public function sign(string $commandKey, array $payload, string $nonce, ?string $expiresAt): string
    {
        $body = json_encode([
            'command_key' => $commandKey,
            'payload' => $payload,
            'nonce' => $nonce,
            'expires_at' => $expiresAt,
        ], JSON_UNESCAPED_SLASHES);

        return hash_hmac('sha256', $body ?: '', config('app.key'));
    }
}

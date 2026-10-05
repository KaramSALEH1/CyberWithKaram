<?php

namespace App\Services\Telegram;

use App\Models\AgentStatus;
use App\Models\Payment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    public function isConfigured(): bool
    {
        return filled(config('telegram.bot_token')) && filled($this->targetChatId());
    }

    /**
     * Resolve the configured destination chat id.
     *
     * Returns null when unset or malformed. There is intentionally no fallback
     * to any legacy/hardcoded chat: notifications are disabled instead of being
     * delivered to an unintended destination.
     */
    public function targetChatId(): ?string
    {
        $chatId = config('telegram.chat_id');

        if (blank($chatId)) {
            return null;
        }

        $chatId = trim((string) $chatId);

        // Telegram ids are numeric; groups/supersgroups start with -100.
        if (! preg_match('/^-?\d+$/', $chatId)) {
            Log::warning('TELEGRAM_CHAT_ID is not numeric - notifications disabled.', [
                'configured_label' => config('telegram.chat_label'),
            ]);

            return null;
        }

        return $chatId;
    }

    public function sendMessage(string $message): bool
    {
        $chatId = $this->targetChatId();

        if (! $this->isConfigured() || $chatId === null) {
            Log::info('Telegram notification skipped (not configured).', ['message' => $message]);

            return false;
        }

        try {
            $response = Http::timeout((int) config('telegram.timeout', 10))->post(
                'https://api.telegram.org/bot'.config('telegram.bot_token').'/sendMessage',
                [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ]
            );
        } catch (ConnectionException $exception) {
            // Notifications are best-effort: never let a network/DNS failure
            // break payment approval, agent registration or any core flow.
            Log::warning('Telegram API unreachable.', ['error' => $exception->getMessage()]);

            return false;
        }

        if (! $response->successful()) {
            Log::warning('Telegram API request failed.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        return true;
    }

    public function notifyNewPaymentReceipt(Payment $payment): bool
    {
        $payment->loadMissing(['user', 'service']);

        $message = implode("\n", [
            '<b>New Payment Receipt Uploaded</b>',
            'User: '.e($payment->user?->name ?? 'Unknown'),
            'Email: '.e($payment->user?->email ?? 'N/A'),
            'Service: '.e($payment->service?->title ?? 'N/A'),
            'Amount: '.number_format((float) $payment->amount, 2),
            'Status: '.e($payment->status),
            'Payment ID: #'.$payment->id,
        ]);

        return $this->sendMessage($message);
    }

    public function notifyAgentOffline(AgentStatus $agentStatus): bool
    {
        $agentStatus->loadMissing(['user', 'service']);

        $message = implode("\n", [
            '<b>Agent Offline Alert</b>',
            'User: '.e($agentStatus->user?->name ?? 'Unknown'),
            'Service: '.e($agentStatus->service?->title ?? 'N/A'),
            'IP: '.e($agentStatus->ip_address ?? 'N/A'),
            'Last heartbeat: '.($agentStatus->last_heartbeat?->toDateTimeString() ?? 'Never'),
        ]);

        return $this->sendMessage($message);
    }
}

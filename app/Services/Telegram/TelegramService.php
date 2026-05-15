<?php

namespace App\Services\Telegram;

use App\Models\AgentStatus;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    public function isConfigured(): bool
    {
        return filled(config('telegram.bot_token')) && filled(config('telegram.chat_id'));
    }

    public function sendMessage(string $message): bool
    {
        if (! $this->isConfigured()) {
            Log::info('Telegram notification skipped (not configured).', ['message' => $message]);

            return false;
        }

        $response = Http::timeout(10)->post(
            'https://api.telegram.org/bot'.config('telegram.bot_token').'/sendMessage',
            [
                'chat_id' => config('telegram.chat_id'),
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]
        );

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

<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\User;
use App\Services\ActionLog\ActionLogService;
use App\Services\Telegram\TelegramService;
use Illuminate\Support\Str;

class PaymentVerificationService
{
    public function __construct(
        private readonly ActionLogService $actionLogService,
        private readonly TelegramService $telegramService
    ) {}

    public function approve(Payment $payment, User $admin): Payment
    {
        $payment->update([
            'status' => 'approved',
            'license_key' => $this->generateLicenseKey(),
            'approved_at' => now(),
        ]);

        $this->actionLogService->log(
            $admin,
            "Approved payment #{$payment->id} for user #{$payment->user_id} on service #{$payment->service_id}. License: {$payment->license_key}"
        );

        $this->telegramService->sendMessage(
            "✅ <b>Payment Approved:</b>\n"
                . "User: {$payment->user?->name} ({$payment->user?->email})\n"
                . "Service: {$payment->service?->title}\n"
                . "Amount: " . number_format((float) $payment->amount, 0) . " SYP\n"
                . "License Key: <code>{$payment->license_key}</code>\n"
                . "Approved by: {$admin->name}\n"
                . "<a href=\"" . route('admin.payments.show', $payment->id) . "\">View Payment Details</a>"
        );

        return $payment->refresh();
    }

    public function reject(Payment $payment, User $admin): Payment
    {
        $payment->update(['status' => 'rejected']);

        $this->actionLogService->log(
            $admin,
            "Rejected payment #{$payment->id} for user #{$payment->user_id} on service #{$payment->service_id}."
        );

        return $payment->refresh();
    }

    public function userHasApprovedAccess(int $userId, int $serviceId, ?string $licenseKey = null): bool
    {
        $query = Payment::query()
            ->where('user_id', $userId)
            ->where('service_id', $serviceId)
            ->where('status', 'approved');

        if ($licenseKey) {
            $query->where('license_key', $licenseKey);
        }

        return $query->exists();
    }

    private function generateLicenseKey(): string
    {
        do {
            $key = 'CWK-' . strtoupper(Str::random(16));
        } while (Payment::where('license_key', $key)->exists());

        return $key;
    }
}

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
        $paymentData = [
            'status' => 'approved',
            'approved_at' => now(),
            'expires_at' => now()->addDays(30), // Default 30 days
        ];

        // Only generate license key for services
        if ($payment->product_type === 'service') {
            $paymentData['license_key'] = $this->generateLicenseKey();
        }

        $payment->update($paymentData);

        // Create entitlement based on product type
        if (in_array($payment->product_type, ['course', 'module', 'lesson'])) {
            \App\Models\Entitlement::updateOrCreate(
                [
                    'user_id' => $payment->user_id,
                    'entitlement_type' => $payment->product_type,
                    'entitlement_id' => $payment->product_id,
                ],
                [
                    'is_active' => true,
                    'starts_at' => now(),
                    'ends_at' => now()->addDays(30),
                ]
            );
        }

        $productTitle = 'Unknown Product';
        if ($payment->product_type === 'service') {
            $productTitle = $payment->service?->title;
        } elseif ($payment->product_type === 'course') {
            $productTitle = \App\Models\Course::find($payment->product_id)?->title;
        } elseif ($payment->product_type === 'module') {
            $productTitle = \App\Models\Module::find($payment->product_id)?->title;
        } elseif ($payment->product_type === 'lesson') {
            $productTitle = \App\Models\Lesson::find($payment->product_id)?->title;
        }

        $this->actionLogService->log(
            $admin,
            "Approved payment #{$payment->id} for user #{$payment->user_id} on {$payment->product_type} #{$payment->product_id}. License: " . ($payment->license_key ?? 'N/A')
        );

        $this->telegramService->sendMessage(
            "✅ <b>Payment Approved:</b>\n"
                . "User: {$payment->user?->name} ({$payment->user?->email})\n"
                . "Product: {$productTitle} (" . ucfirst($payment->product_type) . ")\n"
                . "Amount: " . number_format((float) $payment->amount, 0) . " SYP\n"
                . ($payment->license_key ? "License Key: <code>{$payment->license_key}</code>\n" : "")
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
        return $this->getApprovedPayment($userId, $serviceId, $licenseKey) !== null;
    }

    public function getApprovedPayment(int $userId, int $serviceId, ?string $licenseKey = null): ?Payment
    {
        $query = Payment::query()
            ->where('user_id', $userId)
            ->where('service_id', $serviceId)
            ->where('status', 'approved');

        if ($licenseKey) {
            $query->where('license_key', $licenseKey);
        }

        return $query->latest()->first();
    }

    private function generateLicenseKey(): string
    {
        do {
            $key = 'CWK-' . strtoupper(Str::random(16));
        } while (Payment::where('license_key', $key)->exists());

        return $key;
    }
}

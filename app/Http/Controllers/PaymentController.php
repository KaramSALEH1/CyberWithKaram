<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Payment\StorePaymentDetailsRequest;
use App\Models\Payment;
use App\Models\Service;
use App\Services\Telegram\TelegramService;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function create(Service $service)
    {
        $approvedPayment = null;
        $pendingPayment = null;

        if (auth()->check()) {
            $user = auth()->user();

            $approvedPayment = Payment::query()
                ->where('user_id', $user->id)
                ->where('service_id', $service->id)
                ->where('status', 'approved')
                ->latest()
                ->first();

            if (! $approvedPayment) {
                $pendingPayment = Payment::query()
                    ->where('user_id', $user->id)
                    ->where('service_id', $service->id)
                    ->where('status', 'pending')
                    ->latest()
                    ->first();
            }
        }

        return view('payments.create', compact('service', 'approvedPayment', 'pendingPayment'));
    }

    public function store(StorePaymentDetailsRequest $request, Service $service, TelegramService $telegramService)
    {
        $data = $request->validated();

        $payment = Payment::create([
            'user_id' => $request->user()->id,
            'service_id' => $service->id,
            'amount' => $service->price, // Assuming service price is the expected amount
            'account_name_number' => $data['account_name_number'],
            'transaction_amount' => $data['transaction_amount'],
            'transaction_id_reference' => $data['transaction_id_reference'],
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
        ]);

        // Send Telegram alert
        $message = implode("\n", [
            '🚨 <b>New Payment Request</b>',
            'User: ' . e($request->user()->name),
            'Service: ' . e($service->title),
            'From Account: ' . e($data['account_name_number']),
            'Amount: ' . number_format((float) $data['transaction_amount'], 2) . ' SYP',
            'Ref ID: ' . e($data['transaction_id_reference']),
            'Check Admin Panel to Approve.',
        ]);
        $telegramService->sendMessage($message);

        return redirect()
            ->route('services.pay', $service->slug)
            ->with('success', 'Payment details submitted successfully. Awaiting admin verification.');
    }

    public function mockGlobalPaymentSuccess(Service $service)
    {
        if (! auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to complete the purchase.');
        }

        // Simulate successful global payment
        $payment = Payment::create([
            'user_id' => auth()->id(),
            'service_id' => $service->id,
            'amount' => $service->price,
            'status' => 'approved',
            'license_key' => Str::random(32), // Generate a random license key
            'payment_method' => 'global_mock', // Indicate this was a mock global payment
        ]);

        return redirect()->route('service.show', $service->slug)
            ->with('success', 'Payment successful! Your service is now active.');
    }
}

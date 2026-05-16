<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payment\PaymentVerificationService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with(['user', 'service', 'course'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->latest()
            ->paginate(20);

        return view('admin.payments.index', compact('payments'));
    }

    public function show(Payment $payment)
    {
        return view('admin.payments.show', compact('payment'));
    }

    public function approve(Payment $payment, PaymentVerificationService $verificationService)
    {
        if ($payment->status !== 'pending') {
            return back()->with('error', 'Only pending payments can be approved.');
        }

        $verificationService->approve($payment, request()->user());

        return back()->with('success', "Payment approved. License key: {$payment->license_key}");
    }

    public function reject(Payment $payment, PaymentVerificationService $verificationService)
    {
        if ($payment->status !== 'pending') {
            return back()->with('error', 'Only pending payments can be rejected.');
        }

        $verificationService->reject($payment, request()->user());

        return back()->with('success', 'Payment rejected.');
    }
}

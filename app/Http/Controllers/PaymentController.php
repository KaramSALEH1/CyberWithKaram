<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Payment\StorePaymentDetailsRequest;
use App\Models\Payment;
use App\Models\Service;
use App\Services\Telegram\TelegramService;
use Illuminate\Support\Str;

use App\Models\Course;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function showCheckout($slug, Request $request)
    {
        $product_type = 'service';
        $product = null;
        $productIdField = 'service_id';

        if ($request->routeIs('courses.checkout')) {
            $product = Course::where('slug', $slug)->firstOrFail();
            $product_type = 'course';
            $productIdField = 'product_id';
        } elseif ($request->routeIs('modules.checkout')) {
            $product = Module::where('id', $slug)->firstOrFail(); // Using ID for modules if slug is missing
            $product_type = 'module';
            $productIdField = 'product_id';
        } elseif ($request->routeIs('lessons.checkout')) {
            $product = Lesson::where('slug', $slug)->firstOrFail();
            $product_type = 'lesson';
            $productIdField = 'product_id';
        } else {
            $product = Service::where('slug', $slug)->firstOrFail();
            $product_type = 'service';
            $productIdField = 'service_id';
        }

        $approvedPayment = null;
        $pendingPayment = null;

        if (Auth::check()) {
            $user = Auth::user();

            $approvedPayment = Payment::query()
                ->where('user_id', $user->id)
                ->where($productIdField, $product->id)
                ->where('product_type', $product_type)
                ->where('status', 'approved')
                ->latest()
                ->first();

            if (! $approvedPayment) {
                $pendingPayment = Payment::query()
                    ->where('user_id', $user->id)
                    ->where($productIdField, $product->id)
                    ->where('product_type', $product_type)
                    ->where('status', 'pending')
                    ->latest()
                    ->first();
            }
        }

        return view('payments.checkout', compact('product', 'product_type', 'approvedPayment', 'pendingPayment'));
    }

    public function storePayment(StorePaymentDetailsRequest $request, TelegramService $telegramService)
    {
        $product_type = $request->input('product_type');
        $product_id = $request->input('product_id');

        $product = null;
        $productIdField = 'product_id';

        if ($product_type === 'course') {
            $product = Course::findOrFail($product_id);
        } elseif ($product_type === 'module') {
            $product = Module::findOrFail($product_id);
        } elseif ($product_type === 'lesson') {
            $product = Lesson::findOrFail($product_id);
        } else {
            $product = Service::findOrFail($product_id);
            $productIdField = 'service_id';
        }

        $data = $request->validated();

        $paymentData = [
            'user_id' => $request->user()->id,
            'amount' => $product->price,
            'product_type' => $product_type,
            'account_name_number' => $data['account_name_number'],
            'transaction_amount' => $data['transaction_amount'],
            'transaction_id_reference' => $data['transaction_id_reference'],
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
        ];

        // Explicitly handle product_id vs service_id mapping
        if ($product_type === 'service') {
            $paymentData['service_id'] = $product->id;
            $paymentData['product_id'] = null;
        } else {
            $paymentData['product_id'] = $product->id;
            $paymentData['service_id'] = null; // Explicitly bypass legacy requirement
        }

        $payment = Payment::create($paymentData);

        // Send Telegram alert
        $message = implode("\n", [
            '🚨 <b>New Payment Request</b>',
            'User: ' . e($request->user()->name),
            'Product (' . ucfirst($product_type) . '): ' . e($product->title),
            'From Account: ' . e($data['account_name_number']),
            'Amount: ' . number_format((float) $data['transaction_amount'], 2) . ' SYP',
            'Ref ID: ' . e($data['transaction_id_reference']),
            'Check Admin Panel to Approve.',
        ]);
        $telegramService->sendMessage($message);

        $route = 'services.pay';
        $slug = $product->slug ?? $product->id;

        if ($product_type === 'course') $route = 'courses.checkout';
        elseif ($product_type === 'module') $route = 'modules.checkout';
        elseif ($product_type === 'lesson') $route = 'lessons.checkout';

        return redirect()
            ->route($route, $slug)
            ->with('success', 'Payment details submitted successfully. Awaiting admin verification.');
    }

    public function mockGlobalPaymentSuccess($type, $slug)
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to complete the purchase.');
        }

        $product = null;
        $productIdField = 'product_id';

        if ($type === 'course') {
            $product = Course::where('slug', $slug)->firstOrFail();
        } elseif ($type === 'module') {
            $product = Module::where('id', $slug)->firstOrFail();
        } elseif ($type === 'lesson') {
            $product = Lesson::where('slug', $slug)->firstOrFail();
        } else {
            $product = Service::where('slug', $slug)->firstOrFail();
            $productIdField = 'service_id';
        }

        // Simulate successful global payment
        $paymentData = [
            'user_id' => Auth::user()->id,
            'amount' => $product->price,
            'product_type' => $type,
            'status' => 'approved',
            'license_key' => Str::random(32),
            'payment_method' => 'global_mock',
            'approved_at' => now(),
            'expires_at' => now()->addDays(30),
        ];

        // Explicitly handle product_id vs service_id mapping
        if ($type === 'service') {
            $paymentData['service_id'] = $product->id;
            $paymentData['product_id'] = null;
        } else {
            $paymentData['product_id'] = $product->id;
            $paymentData['service_id'] = null;
        }

        $payment = Payment::create($paymentData);

        // Create entitlement if applicable
        if (in_array($type, ['course', 'module', 'lesson'])) {
            \App\Models\Entitlement::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'entitlement_type' => $type,
                    'entitlement_id' => $product->id,
                ],
                [
                    'is_active' => true,
                    'starts_at' => now(),
                    'ends_at' => now()->addDays(30),
                ]
            );
        }

        if ($type === 'service') {
            return redirect()->route('service.show', $product->slug)
                ->with('success', 'Payment successful! Your service is now active.');
        } elseif ($type === 'course') {
            return redirect()->route('courses.show', $product->slug)
                ->with('success', 'Payment successful! Your academy access is now active.');
        } else {
            // For modules/lessons, redirect back to course page
            $courseSlug = ($type === 'module') ? $product->course->slug : $product->module->course->slug;
            return redirect()->route('courses.show', $courseSlug)
                ->with('success', "Payment successful! Your " . ucfirst($type) . " access is now active.");
        }
    }
}

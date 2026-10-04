<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\StorePaymentDetailsRequest;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Payment;
use App\Models\Service;
use App\Services\Payment\PaymentVerificationService;
use App\Services\Telegram\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
            $product = Module::where('id', $slug)->with('course')->firstOrFail();
            $product_type = 'module';
            $productIdField = 'product_id';
        } elseif ($request->routeIs('lessons.checkout')) {
            $product = Lesson::where('slug', $slug)->with('module.course')->firstOrFail();
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

        $mockSlug = $this->resolveCheckoutSlug($product_type, $product);

        return view('payments.checkout', compact('product', 'product_type', 'approvedPayment', 'pendingPayment', 'mockSlug'));
    }

    public function storePayment(StorePaymentDetailsRequest $request, TelegramService $telegramService)
    {
        $product_type = $request->input('product_type');
        $product_id = (int) $request->input('product_id');

        $product = match ($product_type) {
            'course' => Course::findOrFail($product_id),
            'module' => Module::findOrFail($product_id),
            'lesson' => Lesson::findOrFail($product_id),
            default => Service::findOrFail($product_id),
        };

        $expectedAmount = (float) $product->price;
        $submittedAmount = (float) $request->input('transaction_amount');

        if (abs($submittedAmount - $expectedAmount) > 0.01) {
            throw ValidationException::withMessages([
                'transaction_amount' => 'Transaction amount must match the product price (' . number_format($expectedAmount, 0) . ' SYP).',
            ]);
        }

        $data = $request->validated();

        $paymentData = [
            'user_id' => $request->user()->id,
            'amount' => $expectedAmount,
            'product_type' => $product_type,
            'account_name_number' => $data['account_name_number'],
            'transaction_amount' => $expectedAmount,
            'transaction_id_reference' => $data['transaction_id_reference'],
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
        ];

        if ($product_type === 'service') {
            $paymentData['service_id'] = $product->id;
            $paymentData['product_id'] = null;
        } else {
            $paymentData['product_id'] = $product->id;
            $paymentData['service_id'] = null;
        }

        Payment::create($paymentData);

        $message = implode("\n", [
            '🚨 <b>New Payment Request</b>',
            'User: ' . e($request->user()->name),
            'Product (' . ucfirst($product_type) . '): ' . e($product->title),
            'From Account: ' . e($data['account_name_number']),
            'Amount: ' . number_format($expectedAmount, 2) . ' SYP',
            'Ref ID: ' . e($data['transaction_id_reference']),
            'Check Admin Panel to Approve.',
        ]);
        $telegramService->sendMessage($message);

        $route = match ($product_type) {
            'course' => 'courses.checkout',
            'module' => 'modules.checkout',
            'lesson' => 'lessons.checkout',
            default => 'services.pay',
        };

        return redirect()
            ->route($route, $this->resolveCheckoutSlug($product_type, $product))
            ->with('success', 'Payment details submitted successfully. Awaiting admin verification.');
    }

    public function mockGlobalPaymentSuccess($type, $slug, PaymentVerificationService $verificationService)
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to complete the purchase.');
        }

        $product = match ($type) {
            'course' => Course::where('slug', $slug)->firstOrFail(),
            'module' => Module::where('id', $slug)->with('course')->firstOrFail(),
            'lesson' => Lesson::where('slug', $slug)->with('module.course')->firstOrFail(),
            default => Service::where('slug', $slug)->firstOrFail(),
        };

        $paymentData = [
            'user_id' => Auth::id(),
            'amount' => $product->price,
            'product_type' => $type,
            'status' => 'pending',
            'account_name_number' => 'GLOBAL_MOCK',
            'transaction_amount' => $product->price,
            'transaction_id_reference' => 'MOCK-' . Str::upper(Str::random(8)),
        ];

        if ($type === 'service') {
            $paymentData['service_id'] = $product->id;
            $paymentData['product_id'] = null;
        } else {
            $paymentData['product_id'] = $product->id;
            $paymentData['service_id'] = null;
        }

        $payment = Payment::create($paymentData);
        $verificationService->approve($payment, Auth::user());

        if ($type === 'service') {
            return redirect()->route('service.show', $product->slug)
                ->with('success', 'Payment successful! Your service is now active.');
        }

        if ($type === 'course') {
            return redirect()->route('courses.show', $product->slug)
                ->with('success', 'Payment successful! Your academy access is now active.');
        }

        $courseSlug = $type === 'module'
            ? $product->course->slug
            : $product->module->course->slug;

        return redirect()->route('courses.show', $courseSlug)
            ->with('success', 'Payment successful! Your ' . ucfirst($type) . ' access is now active.');
    }

    private function resolveCheckoutSlug(string $productType, object $product): string|int
    {
        return match ($productType) {
            'module' => $product->id,
            default => $product->slug,
        };
    }
}

@extends('layouts.app')

@section('title', 'Pay for ' . $product->title)

@section('content')
    <section class="py-16 px-6 max-w-3xl mx-auto" x-data="{ paymentMethod: 'local' }">
        <div class="bg-[#0a0a0a] border border-cyan-500/30 shadow-[0_0_15px_rgba(6,182,212,0.1)] rounded-2xl p-8">
            <h1 class="text-3xl font-black mb-2 text-white">{{ $product->title }}</h1>
            <p class="text-cyan-400 text-2xl font-bold mb-6">{{ number_format((float) $product->price, 0) }} SYP</p>

            @if (session('success'))
                <div class="mb-4 rounded-lg border border-green-700 bg-green-900/20 p-4 text-green-300">
                    {{ session('success') }}</div>
            @endif

            @if ($approvedPayment)
                <div class="mb-6 rounded-lg border border-cyan-500/40 bg-cyan-500/10 p-4">
                    <p class="font-bold text-cyan-400">
                        @if($product_type == 'course')
                            Academy Access Granted
                        @elseif($product_type == 'module')
                            Module Access Granted
                        @elseif($product_type == 'lesson')
                            Lesson Access Granted
                        @else
                            Service Activated
                        @endif
                    </p>
                    @if ($product_type == 'service')
                        <p class="text-sm text-gray-300 mt-1">Your license key:</p>
                        <p class="font-mono text-lg mt-2 text-cyan-300">{{ $approvedPayment->license_key }}</p>
                        <p class="text-xs text-gray-400 mt-3">Use this key with your Python Agent and Sanctum API token to
                            fetch the script.</p>
                    @else
                        <p class="text-sm text-gray-300 mt-1">
                            @if($product_type == 'course')
                                You now have full access to all modules and lessons in this track.
                            @elseif($product_type == 'module')
                                You now have full access to all lessons in this module.
                            @else
                                You now have full access to this lesson.
                            @endif
                        </p>
                        @php
                            $redirectUrl = route('courses.show', $product->slug ?? ($product->course->slug ?? $product->module->course->slug));
                        @endphp
                        <a href="{{ $redirectUrl }}"
                            class="inline-block mt-4 bg-cyan-500 text-black px-6 py-2 rounded-lg font-bold hover:bg-cyan-400 transition-colors shadow-[0_0_10px_rgba(6,182,212,0.3)]">
                            @if($product_type == 'lesson')
                                Go to Lesson
                            @else
                                Start Learning
                            @endif
                        </a>
                    @endif
                </div>
            @elseif($pendingPayment)
                <div class="mb-6 rounded-lg border border-cyan-700 bg-cyan-900/20 p-4">
                    <p class="font-bold text-cyan-300 mb-3">Payment Status Tracker</p>
                    <div class="flex items-center justify-between text-sm">
                        <div
                            class="text-center {{ $pendingPayment->status === 'pending' ? 'text-cyan-400 font-semibold' : 'text-gray-500' }}">
                            Payment Submitted
                            @if ($pendingPayment->status === 'pending')
                                <span class="block text-xs">(Awaiting)</span>
                            @endif
                        </div>
                        <div
                            class="flex-1 border-t-2 {{ $pendingPayment->status === 'pending' ? 'border-gray-600' : 'border-cyan-400' }} mx-2">
                        </div>
                        <div
                            class="text-center {{ $pendingPayment->status === 'pending' ? 'text-gray-500' : 'text-cyan-400 font-semibold' }}">
                            Verification in Progress
                            @if ($pendingPayment->status === 'pending')
                                <span class="block text-xs">(Pending Admin Approval)</span>
                            @endif
                        </div>
                        <div
                            class="flex-1 border-t-2 {{ $pendingPayment->status === 'pending' ? 'border-gray-600' : 'border-cyan-400' }} mx-2">
                        </div>
                        <div class="text-center {{ $approvedPayment ? 'text-cyan-400 font-semibold' : 'text-gray-500' }}">
                            Access Granted
                            @if (!$approvedPayment)
                                <span class="block text-xs">(Not Yet)</span>
                            @endif
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-3">Your payment reference: <span
                            class="font-mono text-cyan-400">{{ $pendingPayment->transaction_id_reference }}</span></p>
                </div>
            @elseif($product->price > 0)
                @guest
                    <p class="text-gray-400 mb-4">Please <a href="{{ route('login') }}"
                            class="text-cyan-500 font-bold">login</a> to submit payment.</p>
                @else
                    @php
                        $isAvailable = ($product_type === 'service') ? $product->is_available : $product->is_active;
                    @endphp

                    @if (!$isAvailable)
                        <button disabled
                            class="w-full bg-gray-700 text-gray-400 py-3 rounded-lg font-bold cursor-not-allowed">Payment
                            currently unavailable</button>
                    @else
                        <!-- Payment Method Selection Tabs -->
                        <div class="mb-6 border-b border-gray-700">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                <button @click="paymentMethod = 'local'"
                                    :class="{ 'border-cyan-500 text-cyan-500': paymentMethod === 'local', 'border-transparent text-gray-400 hover:text-gray-200': paymentMethod !== 'local' }"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-colors">
                                    Local Payments (Syria)
                                </button>
                                <button @click="paymentMethod = 'global'"
                                    :class="{ 'border-cyan-500 text-cyan-500': paymentMethod === 'global', 'border-transparent text-gray-400 hover:text-gray-200': paymentMethod !== 'global' }"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-colors">
                                    Global Payments
                                </button>
                            </nav>
                        </div>

                        <!-- Local Payments Section -->
                        <div x-show="paymentMethod === 'local'">
                            <h3 class="text-xl font-bold text-white mb-4">Local Payment Instructions</h3>
                            <p class="text-gray-400 mb-6">Choose one of the methods below and transfer the exact amount. Then,
                                fill the form with your transaction details for admin verification.</p>

                            <!-- Click-to-Copy Cards -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                                <!-- Sham Cash -->
                                <div class="payment-card group">
                                    <h4 class="font-semibold text-white mb-2">Sham Cash</h4>
                                    <p class="text-gray-300 text-sm mb-3">Account: <span id="sham-cash-acc" class="text-cyan-400">0987654321</span>
                                    </p>
                                    <button @click="copyToClipboard($event, 'sham-cash-acc')" class="copy-btn">Copy
                                        Account</button>
                                </div>
                                <!-- Al-Haram -->
                                <div class="payment-card group">
                                    <h4 class="font-semibold text-white mb-2">Al-Haram</h4>
                                    <p class="text-gray-300 text-sm mb-3">Account: <span id="al-haram-acc" class="text-cyan-400">1234567890</span></p>
                                    <button @click="copyToClipboard($event, 'al-haram-acc')" class="copy-btn">Copy
                                        Account</button>
                                </div>
                                <!-- Syrian Bank -->
                                <div class="payment-card group">
                                    <h4 class="font-semibold text-white mb-2">Syrian Bank</h4>
                                    <p class="text-gray-300 text-sm mb-3">Account: <span id="syrian-bank-acc" class="text-cyan-400">1122334455</span>
                                    </p>
                                    <button @click="copyToClipboard($event, 'syrian-bank-acc')" class="copy-btn">Copy
                                        Account</button>
                                </div>
                            </div>

                            <div class="mb-6">
                                <h3 class="text-lg font-semibold text-white mb-3">Payment Instructions</h3>
                                <div
                                    class="bg-[#0f0f0f] border border-gray-700 rounded-lg p-4 text-sm text-gray-300 whitespace-pre-line relative">
                                    @if($product_type == 'service')
                                        {!! nl2br(e($product->payment_instructions ?? 'Contact support for payment details.')) !!}
                                    @else
                                        Please transfer the amount to one of our accounts above and submit your details.
                                    @endif
                                    <button
                                        class="copy-instructions-btn absolute top-2 right-2 bg-gray-800 hover:bg-gray-700 text-white text-xs px-2 py-1 rounded border border-gray-600">
                                        Copy
                                    </button>
                                </div>
                            </div>

                            <form action="{{ route('payments.submit') }}" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="product_type" value="{{ $product_type }}">
                                
                                <div>
                                    <label for="account_name_number" class="block text-xs uppercase text-gray-400 mb-1">Your
                                        Account Name / Number</label>
                                    <input type="text" name="account_name_number" id="account_name_number"
                                        class="w-full bg-[#0f0f0f] border border-gray-700 rounded-lg p-3 text-white focus:border-cyan-500 focus:outline-none transition-colors"
                                        value="{{ old('account_name_number') }}" required>
                                    @error('account_name_number')
                                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="transaction_amount"
                                        class="block text-xs uppercase text-gray-400 mb-1">Transaction Amount (SYP)</label>
                                    <input type="number" name="transaction_amount" id="transaction_amount"
                                        class="w-full bg-[#0f0f0f] border border-gray-700 rounded-lg p-3 text-white focus:border-cyan-500 focus:outline-none transition-colors"
                                        value="{{ old('transaction_amount', number_format((float) $product->price, 0, '.', '')) }}"
                                        required step="1">
                                    @error('transaction_amount')
                                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="transaction_id_reference"
                                        class="block text-xs uppercase text-gray-400 mb-1">Transaction ID / Reference</label>
                                    <input type="text" name="transaction_id_reference" id="transaction_id_reference"
                                        class="w-full bg-[#0f0f0f] border border-gray-700 rounded-lg p-3 text-white focus:border-cyan-500 focus:outline-none transition-colors"
                                        value="{{ old('transaction_id_reference') }}" required>
                                    @error('transaction_id_reference')
                                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="notes" class="block text-xs uppercase text-gray-400 mb-1">Notes
                                        (Optional)
                                    </label>
                                    <textarea name="notes" id="notes" rows="3"
                                        class="w-full bg-[#0f0f0f] border border-gray-700 rounded-lg p-3 text-white focus:border-cyan-500 focus:outline-none transition-colors">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="submit"
                                    class="w-full bg-cyan-500 text-black py-3 rounded-lg font-bold hover:bg-cyan-400 transition-colors shadow-[0_0_15px_rgba(6,182,212,0.3)]">Confirm Local
                                    Payment</button>
                            </form>
                        </div>

                        <!-- Global Payments Section -->
                        <div x-show="paymentMethod === 'global'">
                            <h3 class="text-xl font-bold text-white mb-4">Global Payment Options</h3>
                            <p class="text-gray-400 mb-6">Choose a secure global payment method to complete your purchase
                                instantly.</p>

                            <!-- Payment Option Cards -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                                <div class="payment-card group flex items-center justify-center p-6 bg-gray-900/50 hover:bg-gray-800">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/b/ba/Stripe_logo%2C_revised_2016.png"
                                        alt="Stripe"
                                        class="h-10 object-contain filter grayscale group-hover:grayscale-0 transition-all duration-300">
                                </div>
                                <div class="payment-card group flex items-center justify-center p-6 bg-gray-900/50 hover:bg-gray-800">
                                    <img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_74x46.jpg"
                                        alt="PayPal"
                                        class="h-10 object-contain filter grayscale group-hover:grayscale-0 transition-all duration-300">
                                </div>
                                <div class="payment-card group flex items-center justify-center p-6 bg-gray-900/50 hover:bg-gray-800">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/4/46/Bitcoin.svg" alt="Bitcoin"
                                        class="h-10 object-contain filter grayscale group-hover:grayscale-0 transition-all duration-300">
                                </div>
                                <div class="payment-card group flex items-center justify-center p-6 bg-gray-900/50 hover:bg-gray-800">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/0/04/Visa.svg" alt="Visa"
                                        class="h-10 object-contain filter grayscale group-hover:grayscale-0 transition-all duration-300">
                                </div>
                            </div>

                            <div class="text-center mb-6">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-cyan-900/20 text-cyan-300 border border-cyan-800/50">
                                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Secure Checkout Guaranteed
                                </span>
                            </div>

                            <!-- Mock Global Payment Button -->
                            <button @click="mockGlobalPaymentSuccess()"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-bold transition duration-300 shadow-[0_0_15px_rgba(37,99,235,0.2)]">
                                Pay Securely Now
                            </button>
                        </div>
                    @endif
                @endguest
            @else
                <p class="text-gray-400">This item is free. No payment required.</p>
            @endif
        </div>
    </section>

    @push('scripts')
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('paymentPage', () => ({
                    paymentMethod: 'local',
                    copyToClipboard(event, elementId) {
                        const textToCopy = document.getElementById(elementId).innerText;
                        navigator.clipboard.writeText(textToCopy).then(() => {
                            const originalText = event.target.innerText;
                            event.target.innerText = 'Copied!';
                            setTimeout(() => {
                                event.target.innerText = originalText;
                            }, 2000);
                        }).catch(err => {
                            console.error('Failed to copy: ', err);
                        });
                    },
                    mockGlobalPaymentSuccess() {
                        alert('Simulating global payment success! Redirecting...');
                        window.location.href =
                            '{{ route('payment.mock-global-success', ['type' => $product_type, 'slug' => $product->slug]) }}';
                    }
                }))
            });

            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.copy-instructions-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const instructionsBlock = this.previousElementSibling;
                        const textToCopy = instructionsBlock.innerText.trim();

                        navigator.clipboard.writeText(textToCopy).then(() => {
                            const originalText = this.innerText;
                            this.innerText = 'Copied!';
                            setTimeout(() => {
                                this.innerText = originalText;
                            }, 2000);
                        }).catch(err => {
                            console.error('Failed to copy payment instructions: ', err);
                        });
                    });
                });
            });
        </script>
    @endpush

    <style>
        .payment-card {
            @apply border border-gray-800 rounded-xl p-4 text-center cursor-pointer transition-all duration-300 ease-in-out;
        }

        .payment-card:hover {
            @apply border-cyan-500/50 shadow-[0_0_15px_rgba(6,182,212,0.1)] transform -translate-y-1;
        }

        .copy-btn {
            @apply mt-3 inline-flex items-center px-3 py-1.5 border border-cyan-500/30 text-xs font-medium rounded-md shadow-sm text-cyan-400 bg-cyan-900/20 hover:bg-cyan-900/40 focus:outline-none transition-colors;
        }
    </style>
@endsection

@extends('layouts.admin')

@section('title', 'Payment Verification')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-3xl font-black">Payment <span class="text-karam-green">Verification</span></h1>
            <p class="text-gray-400 text-sm mt-1">Review uploaded receipts and approve licenses.</p>
        </div>

        <div class="space-y-4">
            @forelse($payments as $payment)
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <p class="font-bold text-lg">
                                    @if ($payment->product_type === 'course')
                                        {{ $payment->course?->title ?? 'Deleted Course' }}
                                    @else
                                        {{ $payment->service?->title ?? 'Deleted Service' }}
                                    @endif
                                </p>
                                <span
                                    class="text-[10px] font-black uppercase px-2 py-0.5 rounded {{ $payment->product_type === 'course' ? 'bg-blue-900/30 text-blue-400 border border-blue-800' : 'bg-gray-800 text-gray-400 border border-gray-700' }}">
                                    {{ $payment->product_type }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-400">User: {{ $payment->user?->name }}
                                ({{ $payment->user?->email }})</p>
                            <p class="text-xs text-gray-500 mt-1">Status: <span
                                    class="uppercase {{ $payment->status === 'approved' ? 'text-karam-green' : ($payment->status === 'rejected' ? 'text-red-500' : 'text-cyan-400') }}">{{ $payment->status }}</span>
                            </p>
                            <p class="text-xs text-gray-500">Amount:
                                {{ number_format((float) $payment->transaction_amount, 0) }} SYP (Expected:
                                {{ number_format((float) $payment->amount, 0) }} SYP)</p>
                            <p class="text-xs text-gray-500">From Account: {{ $payment->account_name_number }}</p>
                            <p class="text-xs text-gray-500">Reference ID: {{ $payment->transaction_id_reference }}</p>
                            @if ($payment->notes)
                                <p class="text-xs text-gray-500">Notes: {{ $payment->notes }}</p>
                            @endif
                            @if ($payment->license_key)
                                <p class="text-xs text-karam-green mt-1 font-mono">License: {{ $payment->license_key }}</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            @if ($payment->status === 'pending')
                                <form method="POST" action="{{ route('admin.payments.approve', $payment) }}">
                                    @csrf
                                    <button
                                        class="px-4 py-2 rounded bg-karam-green text-black text-xs font-bold">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.payments.reject', $payment) }}">
                                    @csrf
                                    <button
                                        class="px-4 py-2 rounded bg-red-900 text-red-300 text-xs font-bold">Reject</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 text-gray-400">No payments yet.</div>
            @endforelse
        </div>

        <div>{{ $payments->links() }}</div>
    </div>
@endsection

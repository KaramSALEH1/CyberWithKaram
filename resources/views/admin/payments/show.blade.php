@extends('layouts.admin')

@section('title', 'Payment #' . $payment->id)

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black">Payment <span class="text-cyan-400">#{{ $payment->id }}</span></h1>
                <p class="text-gray-400 text-sm mt-1">{{ $payment->productTitle() }} · {{ ucfirst($payment->product_type) }}</p>
            </div>
            <a href="{{ route('admin.payments.index') }}" class="bg-gray-800 border border-gray-700 px-4 py-2 rounded-lg text-sm font-bold hover:border-cyan-500/50 transition">Back</a>
        </div>

        <div class="bg-[#0a0a0a] border border-gray-800 rounded-2xl overflow-hidden">
            <dl class="divide-y divide-gray-800">
                @foreach ([
                    'User' => $payment->user?->name . ' (' . $payment->user?->email . ')',
                    'Product' => $payment->productTitle(),
                    'Type' => ucfirst($payment->product_type),
                    'Amount' => number_format((float) $payment->amount, 0) . ' SYP',
                    'Status' => strtoupper($payment->status),
                    'Account' => $payment->account_name_number,
                    'Transaction Amount' => number_format((float) $payment->transaction_amount, 0) . ' SYP',
                    'Reference' => $payment->transaction_id_reference,
                    'Notes' => $payment->notes ?: 'N/A',
                    'License Key' => $payment->license_key ?: 'N/A',
                    'Approved At' => $payment->approved_at ?: 'N/A',
                    'Created At' => $payment->created_at,
                ] as $label => $value)
                    <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <dt class="text-xs font-bold text-gray-500 uppercase tracking-widest">{{ $label }}</dt>
                        <dd class="sm:col-span-2 text-sm text-gray-200 {{ $label === 'License Key' ? 'font-mono text-cyan-400' : '' }}">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        @if ($payment->status === 'pending')
            <div class="flex gap-3">
                <form action="{{ route('admin.payments.approve', $payment) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-6 py-2 rounded-lg bg-karam-green text-black text-sm font-bold hover:shadow-[0_0_15px_rgba(0,135,81,0.4)] transition">Approve</button>
                </form>
                <form action="{{ route('admin.payments.reject', $payment) }}" method="POST" onsubmit="return confirm('Reject this payment?');">
                    @csrf
                    <button type="submit" class="px-6 py-2 rounded-lg bg-red-900/40 text-red-400 border border-red-900 text-sm font-bold hover:bg-red-900/60 transition">Reject</button>
                </form>
            </div>
        @endif
    </div>
@endsection

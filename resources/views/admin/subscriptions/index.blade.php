@extends('layouts.admin')

@section('title', 'Subscriptions Report')

@section('content')
    @php
        $statusStyles = [
            'active' => 'bg-green-500/10 text-green-400 border-green-500/30',
            'expiring' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
            'expired' => 'bg-red-500/10 text-red-400 border-red-500/30',
            'pending' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
            'rejected' => 'bg-gray-500/10 text-gray-400 border-gray-500/30',
        ];

        $badge = function ($payment) {
            if ($payment->status === 'pending') {
                return ['pending', 'Pending Approval'];
            }
            if ($payment->status === 'rejected') {
                return ['rejected', 'Rejected'];
            }
            if (! $payment->expires_at) {
                return ['active', 'Active'];
            }
            if ($payment->expires_at->isPast()) {
                return ['expired', 'Expired'];
            }
            if ($payment->expires_at->lessThanOrEqualTo(now()->addDays(7))) {
                return ['expiring', 'Expiring Soon'];
            }
            return ['active', 'Active'];
        };

        $filters = [
            'all' => 'All',
            'active' => 'Active',
            'expiring' => 'Expiring Soon',
            'expired' => 'Expired',
            'pending' => 'Pending Approval',
            'rejected' => 'Rejected',
        ];
    @endphp

    <div class="space-y-8">
        <div>
            <h1 class="text-3xl font-black">Subscriptions <span class="text-karam-green">Report</span></h1>
            <p class="text-gray-400 text-sm mt-1">Subscribers, licence keys and subscription lifecycle.</p>
        </div>

        <!-- Stat cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                <p class="text-xs text-gray-500 uppercase">Total Subscriptions</p>
                <p class="text-3xl font-black text-white">{{ $stats['total_subscriptions'] }}</p>
            </div>
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                <p class="text-xs text-gray-500 uppercase">Active</p>
                <p class="text-3xl font-black text-green-400">{{ $stats['active'] }}</p>
            </div>
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                <p class="text-xs text-gray-500 uppercase">Expiring Soon</p>
                <p class="text-3xl font-black text-yellow-400">{{ $stats['expiring_soon'] }}</p>
            </div>
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                <p class="text-xs text-gray-500 uppercase">Expired</p>
                <p class="text-3xl font-black text-red-400">{{ $stats['expired'] }}</p>
            </div>
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                <p class="text-xs text-gray-500 uppercase">Pending Approval</p>
                <p class="text-3xl font-black text-blue-400">{{ $stats['pending'] }}</p>
            </div>
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                <p class="text-xs text-gray-500 uppercase">Licences Issued</p>
                <p class="text-3xl font-black text-karam-green">{{ $stats['licences_issued'] }}</p>
            </div>
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                <p class="text-xs text-gray-500 uppercase">Active Customers</p>
                <p class="text-3xl font-black text-cyan-400">{{ $stats['customers_with_service'] }}
                    <span class="text-sm text-gray-500 font-normal">/ {{ $stats['total_customers'] }}</span>
                </p>
            </div>
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                <p class="text-xs text-gray-500 uppercase">Active MRR</p>
                <p class="text-3xl font-black text-white">{{ number_format($stats['monthly_recurring_syp'], 0) }}
                    <span class="text-sm text-gray-500 font-normal">SYP</span>
                </p>
            </div>
        </div>

        <!-- Catalogue breakdown -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
            <h2 class="text-lg font-bold text-karam-green mb-4">Services On Sale by Category</h2>
            <div class="flex flex-wrap gap-3">
                @forelse ($stats['by_category'] as $category => $total)
                    <div class="bg-gray-950 border border-gray-800 rounded-lg px-4 py-2 text-sm">
                        <span class="text-gray-400">{{ $category }}</span>
                        <span class="text-white font-bold ms-2">{{ $total }}</span>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No services available.</p>
                @endforelse
            </div>
        </div>

        <!-- Filters + search -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
            <form method="GET" action="{{ route('admin.subscriptions.index') }}"
                class="flex flex-col md:flex-row gap-4 md:items-end">
                <div class="flex-1">
                    <label class="block text-xs uppercase text-gray-400 mb-2" for="q">Search</label>
                    <input id="q" type="text" name="q" value="{{ $search }}"
                        placeholder="Email, name, licence key or service…"
                        class="w-full bg-gray-950 border border-gray-700 rounded-lg p-3 focus:border-karam-green outline-none">
                </div>
                <div class="w-full md:w-56">
                    <label class="block text-xs uppercase text-gray-400 mb-2" for="status">Status</label>
                    <select id="status" name="status"
                        class="w-full bg-gray-950 border border-gray-700 rounded-lg p-3 focus:border-karam-green outline-none">
                        @foreach ($filters as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="bg-karam-green text-black font-bold px-6 py-3 rounded-lg">Apply</button>
                @if ($search !== '' || $status !== 'all')
                    <a href="{{ route('admin.subscriptions.index') }}"
                        class="bg-gray-700 px-6 py-3 rounded-lg font-bold text-center">Reset</a>
                @endif
            </form>
        </div>

        <!-- Subscriptions table -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-950 text-gray-400 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="text-left px-4 py-3">Customer</th>
                        <th class="text-left px-4 py-3">Service</th>
                        <th class="text-left px-4 py-3">Licence Key</th>
                        <th class="text-left px-4 py-3">Status</th>
                        <th class="text-left px-4 py-3">Started</th>
                        <th class="text-left px-4 py-3">Expires</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subscriptions as $payment)
                        @php
                            $badgeState = $badge($payment);
                            $key = $badgeState[0];
                            $label = $badgeState[1];
                        @endphp
                        <tr class="border-t border-gray-800 hover:bg-gray-800/40">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.payments.show', $payment) }}"
                                    class="text-karam-green hover:underline font-medium">
                                    {{ $payment->user?->name ?? 'Deleted user' }}
                                </a>
                                <p class="text-gray-500 text-xs">{{ $payment->user?->email ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-300">
                                {{ $payment->service?->icon }} {{ $payment->service?->title ?? '—' }}
                                @if ($payment->service)
                                    <p class="text-xs text-gray-500">{{ $payment->service->category }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($payment->license_key)
                                    <code class="text-xs text-cyan-400 bg-black/40 border border-gray-700 rounded px-2 py-1 break-all overflow-x-auto whitespace-pre-wrap">
                                        {{ $payment->license_key }}
                                    </code>
                                @else
                                    <span class="text-gray-600 text-xs">Not issued</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-block border rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest {{ $statusStyles[$key] }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs">
                                {{ $payment->approved_at?->format('Y-m-d') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs">
                                @if ($payment->expires_at)
                                    <span class="{{ $payment->expires_at->isPast() ? 'text-red-400' : 'text-gray-300' }}">
                                        {{ $payment->expires_at->format('Y-m-d') }}
                                    </span>
                                    <p class="text-gray-500">{{ $payment->expires_at->diffForHumans() }}</p>
                                @else
                                    <span class="text-gray-600">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-gray-500">
                                No subscriptions match the current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $subscriptions->links() }}</div>
    </div>
@endsection
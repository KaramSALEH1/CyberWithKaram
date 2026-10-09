@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-[#0a0a0a] text-gray-100 font-sans py-12 sm:py-20 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 overflow-hidden">
        <div class="w-full min-w-0">
            <div class="mb-12">
                <h1 class="text-2xl sm:text-4xl font-sans font-bold text-white mb-2 tracking-tight">
                    <span class="text-karam-green">My Tools</span>
                    &amp; Active Subscriptions
                </h1>
                <p class="text-gray-500 font-medium">Your active CyberLogia subscriptions. Each agent below is
                    a self-installing automated service &mdash; download it, run it, and it reports back
                    every 60 seconds.</p>
            </div>

            @if ($payments->isEmpty())
                <div
                    class="relative group bg-[#0a0a0a] border border-dashed border-white/10 rounded-3xl p-6 sm:p-16 text-center overflow-hidden">
                    <div
                        class="absolute inset-0 bg-cyan-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    </div>
                    <div class="relative z-10">
                        <div class="text-6xl mb-6">🛡️</div>
                        <h3 class="text-xl sm:text-2xl font-sans font-bold text-white mb-3 sm:mb-4">
                            Your tools are empty.
                        </h3>
                        <p class="text-gray-500 mb-6 sm:mb-10 max-w-md mx-auto text-sm sm:text-base">
                            Explore our services to get started and equip your infrastructure with
                            elite tools.
                        </p>
                        <a href="{{ route('services') }}"
                            class="inline-flex items-center gap-2 px-8 py-4 bg-cyan-500 text-black font-bold rounded-xl hover:bg-cyan-400 transition-all shadow-[0_0_30px_rgba(0,242,255,0.2)]">
                            Browse Services
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 w-full mb-10 sm:mb-14">
                    @foreach ($payments as $payment)
                        @if (!$payment->service) @continue @endif
                        <div
                            class="w-full min-w-0 flex-1 bg-slate-900/80 p-5 rounded-xl border border-slate-800 hover:border-cyan-500/30 transition-all duration-300 group overflow-hidden">
                            <div class="flex flex-col">
                                <div class="flex justify-between items-start mb-4 gap-3">
                                <div class="p-2 sm:p-3 bg-gray-900/50 rounded-xl border border-white/5">
                                    <div class="text-2xl">{{ $payment->service->icon ?? '🛠️' }}</div>
                                </div>
                                <div class="bg-cyan-500/5 border border-cyan-500/20 px-2 sm:px-3 py-1 rounded-full text-[10px] sm:text-xs font-bold uppercase tracking-widest whitespace-nowrap">
                                    @if($payment->expires_at && $payment->expires_at->isPast())
                                        <span class="text-[10px] font-bold text-red-500 uppercase tracking-widest">Expired</span>
                                    @else
                                        <span class="text-[10px] font-bold text-cyan-400 uppercase tracking-widest">Active</span>
                                    @endif
                                </div>
                                </div>

                                <h3 class="text-base sm:text-xl font-sans font-bold text-white leading-tight mb-3 sm:mb-4">
                                    {{ $payment->service->title }}
                                </h3>

                            @php
                                $categoryStyles = [
                                    'Blue Team' => 'bg-blue-500/10 border-blue-400/30 text-blue-300',
                                    'Red Team' => 'bg-red-500/10 border-red-400/30 text-red-300',
                                    'Cloud Security' => 'bg-sky-500/10 border-sky-400/30 text-sky-300',
                                ];
                                $badgeStyle = $categoryStyles[$payment->service->category] ?? 'bg-cyan-500/10 border-cyan-400/30 text-cyan-300';
                            @endphp

                            <div class="flex flex-wrap gap-2 mb-5">
                                <span
                                    class="inline-flex items-center gap-1.5 border {{ $badgeStyle }} px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ $payment->service->category ?: 'Security' }}
                                </span>
                                @if ($payment->service->is_automated)
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-emerald-500/10 border border-emerald-400/30 text-emerald-300 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest">
                                        Automated Agent Service
                                    </span>
                                @endif
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-[10px] font-mono text-gray-500 uppercase tracking-widest mb-1">Subscription Ends (expires_at)</p>
                                    @if ($payment->expires_at)
                                        <div class="flex items-center justify-between gap-3 bg-black/50 border border-white/5 p-3 rounded-lg">
                                            <span
                                                class="font-mono text-xs {{ $payment->expires_at->isPast() ? 'text-red-400' : 'text-emerald-400' }}">
                                                {{ $payment->expires_at->format('Y-m-d H:i') }} UTC
                                            </span>
                                            @if ($payment->expires_at->isPast())
                                                <span class="text-[10px] font-bold text-red-400 uppercase tracking-widest">Expired</span>
                                            @else
                                                <span
                                                    class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest whitespace-nowrap">
                                                    {{ $payment->expires_at->diffForHumans() }} left
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                            <div class="bg-black/50 border border-white/5 p-2.5 sm:p-3 rounded-lg">
                                                <span class="font-sans text-xs text-gray-500">No expiry date set</span>
                                            </div>
                                    @endif
                                </div>

                                <div>
                                    <p class="text-[10px] sm:text-xs font-sans text-gray-500 uppercase tracking-widest mb-1.5">License Key</p>
                                    <div class="w-full min-w-0 overflow-x-auto p-3 bg-slate-950 rounded text-xs font-mono break-all whitespace-pre-wrap select-all text-cyan-500">{{ $payment->license_key }}</div>
                                </div>

                                <div>
                                    <p class="text-[10px] sm:text-xs font-sans text-gray-500 uppercase tracking-widest mb-1.5">Linux / macOS</p>
                                    <div class="w-full min-w-0 overflow-x-auto p-3 bg-slate-950 rounded text-xs font-mono break-all whitespace-pre-wrap select-all text-green-400">curl -fsSL "{{ request()->schemeAndHttpHost() }}/my-tools/download-agent/{{ $payment->service->id }}/{{ $payment->license_key }}" -o agent_bootstrapper.py &amp;&amp; python3 agent_bootstrapper.py</div>
                                </div>

                                <div>
                                    <p class="text-[10px] sm:text-xs font-sans text-gray-500 uppercase tracking-widest mb-1.5">Windows PowerShell</p>
                                    <div class="w-full min-w-0 overflow-x-auto p-3 bg-slate-950 rounded text-xs font-mono break-all whitespace-pre-wrap select-all text-green-400">Invoke-WebRequest -Uri "{{ request()->schemeAndHttpHost() }}/my-tools/download-agent/{{ $payment->service->id }}/{{ $payment->license_key }}" -OutFile agent_bootstrapper.py; python agent_bootstrapper.py</div>
                                </div>

                                <a href="{{ route('my-tools.download-agent', ['service_id' => $payment->service->id, 'license_key' => $payment->license_key]) }}"
                                    class="w-full flex items-center justify-center gap-2 px-3 sm:px-6 py-2.5 sm:py-4 bg-cyan-500 hover:bg-cyan-400 text-black border border-cyan-400 rounded-xl font-bold text-sm transition-all shadow-[0_0_30px_rgba(0,242,255,0.2)]">
                                    Download agent_bootstrapper.py
                                </a>
                                <p class="text-[10px] sm:text-xs font-sans text-gray-600 uppercase tracking-widest text-center">
                                    Executable &middot; Linux, macOS &amp; Windows
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif

            <div class="w-full min-w-0 flex-1 bg-slate-900/80 p-5 rounded-xl border border-slate-800">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                    <div class="max-w-xl">
                        <h3 class="text-xl sm:text-2xl font-sans font-bold text-white mb-2">Sanctum API Token</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Authenticate your agent with this token. Regenerated each visit — copy before leaving.</p>
                    </div>
                    <div class="w-full min-w-0 flex-1 max-w-md sm:flex-shrink-0">
                        <div
                            class="w-full min-w-0 overflow-x-auto p-3 bg-slate-950 rounded text-xs font-mono break-all whitespace-pre-wrap select-all text-green-400">{{ $sanctumToken }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

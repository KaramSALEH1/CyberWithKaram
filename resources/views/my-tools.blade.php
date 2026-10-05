@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-[#0a0a0a] text-gray-100 font-sans py-20 px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-12">
                <h1 class="text-4xl font-mono font-bold text-white mb-2 tracking-tight">Your <span
                        class="text-cyan-400">Arsenal</span></h1>
                <p class="text-gray-500 font-medium">Your active CyberLogia subscriptions. Each agent below is
                    a self-installing automated service &mdash; download it, run it, and it reports back
                    every 60 seconds.</p>
            </div>

            @if ($payments->isEmpty())
                <div
                    class="relative group bg-[#0a0a0a] border border-dashed border-white/10 rounded-3xl p-20 text-center overflow-hidden">
                    <div
                        class="absolute inset-0 bg-cyan-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    </div>
                    <div class="relative z-10">
                        <div class="text-6xl mb-6">🛡️</div>
                        <h3 class="text-2xl font-mono font-bold text-white mb-4">Your arsenal is empty.</h3>
                        <p class="text-gray-500 mb-10 max-w-md mx-auto">Explore our services to get started and equip your
                            infrastructure with elite tools.</p>
                        <a href="{{ route('services') }}"
                            class="inline-flex items-center gap-2 px-8 py-4 bg-cyan-500 text-black font-bold rounded-xl hover:bg-cyan-400 transition-all shadow-[0_0_30px_rgba(0,242,255,0.2)]">
                            Browse Services
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-16">
                    @foreach ($payments as $payment)
                        @if (!$payment->service) @continue @endif
                        <div
                            class="bg-[#0a0a0a] border border-white/5 rounded-2xl p-6 hover:border-cyan-500/30 transition-all duration-300 group">
                            <div class="flex justify-between items-start mb-6">
                                <div class="p-3 bg-gray-900/50 rounded-xl border border-white/5">
                                    <div class="text-2xl">{{ $payment->service->icon ?? '🛠️' }}</div>
                                </div>
                                <div class="bg-cyan-500/5 border border-cyan-500/20 px-3 py-1 rounded-full">
                                    @if($payment->expires_at && $payment->expires_at->isPast())
                                        <span class="text-[10px] font-bold text-red-500 uppercase tracking-widest">Expired</span>
                                    @else
                                        <span class="text-[10px] font-bold text-cyan-400 uppercase tracking-widest">Active</span>
                                    @endif
                                </div>

                                <h3 class="text-xl font-mono font-bold text-white mb-3">{{ $payment->service->title }}</h3>

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
                                        <div class="bg-black/50 border border-white/5 p-3 rounded-lg">
                                            <span class="font-mono text-xs text-gray-500">No expiry date set</span>
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    <p class="text-[10px] font-mono text-gray-500 uppercase tracking-widest mb-1">License Key</p>
                                    <code class="bg-black/50 border border-white/5 p-2 rounded block text-xs text-cyan-500 font-mono break-all">{{ $payment->license_key }}</code>
                                </div>

                                <div>
                                    <p class="text-[10px] font-mono text-gray-500 uppercase tracking-widest mb-1">Linux / macOS</p>
                                    <code class="bg-black/80 border border-cyan-500/20 p-3 rounded-lg block text-[10px] text-green-400 font-mono break-all">curl -fsSL "{{ $baseUrl }}/my-tools/download-agent/{{ $payment->service->id }}/{{ $payment->license_key }}" -o agent_bootstrapper.py &amp;&amp; python3 agent_bootstrapper.py</code>
                                </div>

                                <div>
                                    <p class="text-[10px] font-mono text-gray-500 uppercase tracking-widest mb-1">Windows PowerShell</p>
                                    <code class="bg-black/80 border border-cyan-500/20 p-3 rounded-lg block text-[10px] text-green-400 font-mono break-all">Invoke-WebRequest -Uri "{{ $baseUrl }}/my-tools/download-agent/{{ $payment->service->id }}/{{ $payment->license_key }}" -OutFile agent_bootstrapper.py; python agent_bootstrapper.py</code>
                                </div>

                                <a href="{{ route('my-tools.download-agent', ['service_id' => $payment->service->id, 'license_key' => $payment->license_key]) }}"
                                    class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-cyan-500 hover:bg-cyan-400 text-black border border-cyan-400 rounded-xl font-bold text-sm transition-all shadow-[0_0_30px_rgba(0,242,255,0.2)]">
                                    Download agent_bootstrapper.py
                                </a>
                                <p class="text-[10px] font-mono text-gray-600 uppercase tracking-widest text-center">
                                    Executable &middot; Linux, macOS &amp; Windows
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="bg-[#0a0a0a] border border-cyan-500/20 rounded-3xl p-8 md:p-12">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-8">
                    <div class="max-w-xl">
                        <h3 class="text-2xl font-mono font-bold text-white mb-2">Sanctum API Token</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Authenticate your agent with this token. Regenerated each visit — copy before leaving.</p>
                    </div>
                    <div class="flex-grow max-w-md">
                        <code
                            class="bg-black/80 border border-cyan-500/20 p-4 rounded-xl block text-xs text-green-400 font-mono break-all">{{ $sanctumToken }}</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

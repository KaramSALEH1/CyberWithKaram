@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-[#050505] text-gray-100 font-sans py-20 px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-12">
                <h1 class="text-4xl font-mono font-bold text-white mb-2 tracking-tight">Your <span
                        class="text-cyan-400">Arsenal</span></h1>
                <p class="text-gray-500 font-medium">Manage and deploy your active cybersecurity assets.</p>
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
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-16">
                    @foreach ($payments as $payment)
                        <div
                            class="bg-[#0a0a0a] border border-white/5 rounded-2xl p-6 hover:border-cyan-500/30 transition-all duration-300 group">
                            <div class="flex justify-between items-start mb-6">
                                <div class="p-3 bg-gray-900/50 rounded-xl border border-white/5">
                                    <div class="text-2xl">{{ $payment->service->icon ?? '🛠️' }}</div>
                                </div>
                                <div class="bg-cyan-500/5 border border-cyan-500/20 px-3 py-1 rounded-full">
                                    <span
                                        class="text-[10px] font-bold text-cyan-400 uppercase tracking-widest">Active</span>
                                </div>
                            </div>

                            <h3 class="text-xl font-mono font-bold text-white mb-2">{{ $payment->service->title }}</h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-[10px] font-mono text-gray-500 uppercase tracking-widest mb-1">License
                                        Key</p>
                                    <code
                                        class="bg-black/50 border border-white/5 p-2 rounded block text-xs text-cyan-500 font-mono break-all">{{ $payment->license_key }}</code>
                                </div>

                                <a href="{{ route('my-tools.download-agent', ['service_id' => $payment->service->id, 'license_key' => $payment->license_key]) }}"
                                    class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-white/5 hover:bg-cyan-500 hover:text-black border border-white/10 rounded-xl font-bold text-sm transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Download Agent
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="bg-[#0a0a0a] border border-white/5 rounded-3xl p-8 md:p-12">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-8">
                    <div class="max-w-xl">
                        <h3 class="text-2xl font-mono font-bold text-white mb-2">Sanctum API Access</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Use this token to authenticate your agents with the
                            central API. Keep this token confidential to prevent unauthorized access.</p>
                    </div>
                    <div class="flex-grow max-w-md">
                        <div class="relative">
                            <code
                                class="bg-black/80 border border-white/10 p-4 rounded-xl block text-xs text-green-400 font-mono break-all pr-12">{{ $sanctumToken }}</code>
                            <button onclick="navigator.clipboard.writeText('{{ $sanctumToken }}')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-white transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Scripts for My Tools page
        </script>
    @endpush
@endsection

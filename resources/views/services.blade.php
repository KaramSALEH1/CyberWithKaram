@extends('layouts.app')
@section('title', 'Our Services')

@section('content')
    <div class="min-h-screen bg-[#050505] text-gray-100 font-sans selection:bg-cyan-500/30">
        <section class="py-24 px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="text-center mb-20">
                <h1 class="text-5xl md:text-7xl font-black tracking-tighter mb-6 text-white uppercase italic">
                    The <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-600">Arsenal</span>
                </h1>
                <p class="text-gray-500 text-lg max-w-2xl mx-auto font-medium">
                    Professional-grade cybersecurity tools and defensive assets.
                    Equip your infrastructure with elite-level protection.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($services as $service)
                    <div
                        class="bento-card group relative bg-[#0a0a0a] border border-[#00f2ff33] rounded-2xl p-8 flex flex-col transition-all duration-500 hover:border-cyan-400 hover:shadow-[0_0_30px_rgba(0,242,255,0.15)] overflow-hidden">
                        <!-- Background Glow -->
                        <div
                            class="absolute -top-24 -right-24 w-48 h-48 bg-cyan-500/5 rounded-full blur-3xl group-hover:bg-cyan-500/10 transition-all duration-500">
                        </div>

                        <div class="relative z-10">
                            <div class="flex justify-between items-start mb-6">
                                <div class="p-3 bg-gray-900/50 rounded-xl border border-white/5">
                                    @if ($service->logo_url)
                                        <img src="{{ $service->logo_url }}" alt="{{ $service->title }}"
                                            class="h-10 w-10 object-contain">
                                    @else
                                        <div class="text-3xl">{{ $service->icon ?? '🛡️' }}</div>
                                    @endif
                                </div>

                                @if ($service->is_available)
                                    <div
                                        class="flex items-center gap-2 bg-green-500/5 border border-green-500/20 px-3 py-1 rounded-full">
                                        <span class="relative flex h-2 w-2">
                                            <span
                                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                            <span
                                                class="relative inline-flex rounded-full h-2 w-2 bg-green-500 pulse-green"></span>
                                        </span>
                                        <span
                                            class="text-[10px] font-bold text-green-400 uppercase tracking-widest">Available</span>
                                    </div>
                                @else
                                    <div
                                        class="flex items-center gap-2 bg-red-500/5 border border-red-500/20 px-3 py-1 rounded-full">
                                        <span class="h-2 w-2 rounded-full bg-red-500/50"></span>
                                        <span
                                            class="text-[10px] font-bold text-red-400 uppercase tracking-widest">Locked</span>
                                    </div>
                                @endif
                            </div>

                            <p class="text-[10px] font-mono text-cyan-500 uppercase tracking-[0.2em] mb-2">
                                {{ $service->category }}</p>
                            <h2
                                class="text-2xl font-mono font-bold text-white mb-4 tracking-tight group-hover:text-cyan-400 transition-colors">
                                {{ $service->title }}</h2>
                            <p class="text-gray-400 text-sm leading-relaxed mb-8 line-clamp-3">{{ $service->description }}
                            </p>
                        </div>

                        <div class="mt-auto relative z-10">
                            <div class="flex items-baseline gap-2 mb-6">
                                @if ($service->requiresPayment())
                                    <span
                                        class="text-2xl font-mono font-bold text-white">{{ number_format((float) $service->price, 0) }}</span>
                                    <span class="text-xs font-medium text-gray-500 uppercase">SYP</span>
                                @else
                                    <span class="text-lg font-mono font-bold text-gray-400 italic">Custom Quote</span>
                                @endif
                            </div>

                            <div class="flex flex-col gap-3">
                                <a href="{{ route('service.show', $service->slug) }}" class="btn-primary-cyan-v2">
                                    <span>Explore</span>
                                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </a>

                                @if ($service->is_available)
                                    <a href="{{ route('services.pay', $service->slug) }}" class="btn-buy-now-cyan">
                                        Buy Now
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-20 text-center border border-dashed border-white/10 rounded-3xl">
                        <p class="text-gray-500 font-mono italic">No assets currently deployed in the arsenal.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection

<style>
    @keyframes pulse-green {
        0% {
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4);
        }

        70% {
            box-shadow: 0 0 0 6px rgba(34, 197, 94, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
        }
    }

    .pulse-green {
        animation: pulse-green 2s infinite;
    }

    .btn-primary-cyan-v2 {
        @apply relative flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-bold text-sm transition-all duration-300;
        background: linear-gradient(90deg, #00f2ff, #0087ff);
        color: #000;
        box-shadow: 0 0 20px rgba(0, 242, 255, 0.3);
    }

    .btn-primary-cyan-v2:hover {
        box-shadow: 0 0 30px rgba(0, 242, 255, 0.5);
        transform: translateY(-1px);
    }

    .btn-buy-now-cyan {
        @apply flex items-center justify-center px-6 py-3 rounded-xl font-bold text-sm border border-cyan-500/30 text-cyan-400 transition-all duration-300;
        background: rgba(0, 242, 255, 0.05);
    }

    .btn-buy-now-cyan:hover {
        background: rgba(0, 242, 255, 0.1);
        border-color: #00f2ff;
        color: #fff;
    }

    .bento-card {
        background: #0a0a0a;
    }
</style>

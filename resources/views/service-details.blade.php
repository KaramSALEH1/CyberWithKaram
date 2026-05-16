@extends('layouts.app')

@section('title', $service->title)

@section('content')
    <div class="min-h-screen bg-[#050505] text-gray-100 font-sans" x-data="{ openDocs: false }">
        <!-- Hero Section -->
        <section class="relative py-20 md:py-32 px-6 lg:px-8 overflow-hidden bg-[#050505] mesh-gradient">
            <div class="relative max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-12">
                <div class="flex-1 text-center md:text-left">
                    <p class="text-cyan-400 text-sm uppercase tracking-widest mb-2">{{ $service->category }}</p>
                    <h1 class="text-5xl md:text-6xl font-extrabold leading-tight mb-4 text-white">
                        {{ $service->title }}
                    </h1>
                    <span class="inline-block bg-cyan-600 text-white text-xs font-bold px-3 py-1 rounded-full mb-6">
                        @if ($service->is_available)
                            Stable
                        @else
                            Coming Soon
                        @endif
                    </span>
                    <p class="text-gray-300 text-lg md:text-xl mb-8 max-w-2xl mx-auto md:mx-0">
                        {{ $service->description }}
                    </p>

                    <!-- Action Buttons (Hero) -->
                    <div class="flex flex-wrap gap-4 mt-8 justify-center md:justify-start">
                        @guest
                            <a href="{{ route('register') }}" class="btn-primary-cyan">Get Started</a>
                            <a href="{{ route('login') }}" class="btn-secondary-gray">Login to Access</a>
                        @else
                            @php
                                $canDownload = $hasApprovedAccess || Auth::user()->is_admin;
                                $downloadUrl = $canDownload
                                    ? route('my-tools.download-agent', [
                                        'service_id' => $service->id,
                                        'license_key' => $userLicenseKey ?? 'ADMIN-TEST-MODE',
                                    ])
                                    : null;
                            @endphp

                            @if ($canDownload)
                                <a href="{{ $downloadUrl }}"
                                    class="btn-primary-cyan px-10 py-4 text-lg shadow-[0_0_40px_rgba(0,242,255,0.3)] hover:shadow-[0_0_50px_rgba(0,242,255,0.5)] transform hover:-translate-y-1">
                                    Download Agent
                                </a>
                                <button @click="openDocs = true"
                                    class="px-8 py-4 bg-transparent border border-cyan-500/30 text-cyan-400 hover:text-white hover:bg-cyan-500/10 font-bold rounded-xl transition-all">
                                    View Documentation
                                </button>
                                @if (Auth::user()->is_admin && !$hasApprovedAccess)
                                    <p
                                        class="w-full text-[10px] font-mono text-yellow-500/50 mt-2 uppercase tracking-widest italic">
                                        Admin Overrule Active</p>
                                @endif
                            @else
                                @if ($service->is_available)
                                    <a href="{{ route('services.pay', $service->slug) }}"
                                        class="px-8 py-4 bg-cyan-500 hover:bg-cyan-400 text-black font-bold rounded-xl shadow-[0_0_20px_rgba(0,242,255,0.4)] transition-all transform hover:scale-105">
                                        Request Access
                                    </a>
                                    <button @click="openDocs = true"
                                        class="px-8 py-4 bg-transparent border border-cyan-500/30 text-cyan-400 hover:text-white hover:bg-cyan-500/10 font-bold rounded-xl transition-all">
                                        View Documentation
                                    </button>
                                @else
                                    <button disabled class="btn-disabled">Service Locked</button>
                                @endif
                            @endif
                        @endguest
                    </div>
                </div>
                <div class="flex-shrink-0">
                    @if ($service->logo_url)
                        <img src="{{ $service->logo_url }}" alt="{{ $service->title }}"
                            class="w-48 h-48 object-contain drop-shadow-lg">
                    @else
                        <div class="text-8xl p-8 bg-gray-800 rounded-full shadow-lg">{{ $service->icon ?? '🛡️' }}</div>
                    @endif
                </div>
            </div>
        </section>

        <!-- Documentation Modal -->
        <div x-show="openDocs"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-cloak>

            <div @click.away="openDocs = false"
                class="bg-[#0a0a0a] border border-cyan-500/30 w-full max-w-4xl max-h-[85vh] overflow-hidden rounded-2xl shadow-[0_0_50px_rgba(0,242,255,0.15)] flex flex-col">
                <div class="p-6 border-b border-white/10 flex justify-between items-center bg-cyan-500/5">
                    <h3 class="text-xl font-mono text-cyan-400">Technical Documentation: {{ $service->title }}</h3>
                    <button @click="openDocs = false" class="text-gray-400 hover:text-white text-2xl">&times;</button>
                </div>

                <div class="p-8 overflow-y-auto custom-scrollbar prose prose-invert max-w-none">
                    {!! $service->full_description ?? 'No detailed documentation available yet.' !!}
                </div>

                <div class="p-4 border-t border-white/5 text-right">
                    <button @click="openDocs = false"
                        class="px-6 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-all">Close</button>
                </div>
            </div>
        </div>

        <!-- Features Grid Section -->
        <section class="py-16 px-6 lg:px-8 bg-gray-900">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-4xl font-bold text-center mb-12 text-white">Key Features</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="feature-card">
                        <div class="text-4xl text-cyan-500 mb-4">⚡</div>
                        <h3 class="text-xl font-semibold mb-2">Fast Execution</h3>
                        <p class="text-gray-400">Leverage optimized scripts for rapid task completion and immediate results.
                        </p>
                    </div>
                    <div class="feature-card">
                        <div class="text-4xl text-cyan-500 mb-4">👻</div>
                        <h3 class="text-xl font-semibold mb-2">Stealth Mode</h3>
                        <p class="text-gray-400">Operate discreetly with advanced evasion techniques to avoid detection.</p>
                    </div>
                    <div class="feature-card">
                        <div class="text-4xl text-cyan-500 mb-4">🔌</div>
                        <h3 class="text-xl font-semibold mb-2">API Access</h3>
                        <p class="text-gray-400">Integrate seamlessly with your existing systems using our robust API.</p>
                    </div>
                    <div class="feature-card">
                        <div class="text-4xl text-cyan-500 mb-4">🛡️</div>
                        <h3 class="text-xl font-semibold mb-2">Robust Security</h3>
                        <p class="text-gray-400">Built with security in mind, protecting your operations and data.</p>
                    </div>
                    <div class="feature-card">
                        <div class="text-4xl text-cyan-500 mb-4">🌍</div>
                        <h3 class="text-xl font-semibold mb-2">Global Reach</h3>
                        <p class="text-gray-400">Deploy and manage agents across diverse geographical locations
                            effortlessly.</p>
                    </div>
                    <div class="feature-card">
                        <div class="text-4xl text-cyan-500 mb-4">📊</div>
                        <h3 class="text-xl font-semibold mb-2">Detailed Reporting</h3>
                        <p class="text-gray-400">Gain insights with comprehensive reports and real-time analytics.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Technical Overview: How it Works -->
        <section class="py-16 px-6 lg:px-8 bg-[#050505]">
            <div class="max-w-7xl mx-auto text-center">
                <h2 class="text-4xl font-bold mb-12 text-white">How It Works</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="how-it-works-step">
                        <div class="text-5xl text-cyan-400 mb-4">1.</div>
                        <h3 class="text-xl font-semibold mb-2">Purchase Service</h3>
                        <p class="text-gray-400">Select your desired cybersecurity tool and proceed to payment.</p>
                    </div>
                    <div class="how-it-works-step">
                        <div class="text-5xl text-cyan-400 mb-4">2.</div>
                        <h3 class="text-xl font-semibold mb-2">Admin Approval</h3>
                        <p class="text-gray-400">Our team verifies your payment and activates your license key.</p>
                    </div>
                    <div class="how-it-works-step">
                        <div class="text-5xl text-cyan-400 mb-4">3.</div>
                        <h3 class="text-xl font-semibold mb-2">Download Agent</h3>
                        <p class="text-gray-400">Access your personalized agent script with pre-configured settings.</p>
                    </div>
                    <div class="how-it-works-step">
                        <div class="text-5xl text-cyan-400 mb-4">4.</div>
                        <h3 class="text-xl font-semibold mb-2">Run & Execute</h3>
                        <p class="text-gray-400">Deploy the agent on your target system to begin operations.</p>
                    </div>
                </div>
            </div>
        </section>

    </div>
    @push('scripts')
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.copy-to-clipboard-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const codeBlock = this.previousElementSibling;
                        const textToCopy = codeBlock.innerText;

                        navigator.clipboard.writeText(textToCopy).then(() => {
                            const originalText = this.innerText;
                            this.innerText = 'Copied!';
                            setTimeout(() => {
                                this.innerText = originalText;
                            }, 2000);
                        }).catch(err => {
                            console.error('Failed to copy: ', err);
                        });
                    });
                });
            });
        </script>
    @endpush

    <style>
        .mesh-gradient {
            background-image: radial-gradient(at 10% 20%, hsl(218, 50%, 10%) 0, transparent 50%),
                radial-gradient(at 90% 80%, hsl(180, 70%, 20%) 0, transparent 50%);
            background-size: cover;
            background-position: center;
        }

        .btn-glowing-cyan {
            @apply bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-3 px-8 rounded-lg transition duration-300 ease-in-out shadow-lg;
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.6), 0 0 30px rgba(0, 255, 255, 0.4);
        }

        .btn-primary-cyan {
            @apply bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-3 px-8 rounded-lg transition duration-300 ease-in-out shadow-lg;
        }

        .btn-secondary-gray {
            @apply bg-gray-700 hover:bg-gray-600 text-gray-200 font-bold py-3 px-8 rounded-lg transition duration-300 ease-in-out border border-gray-600;
        }

        .btn-disabled {
            @apply bg-gray-700 text-gray-400 py-3 px-8 rounded-lg font-bold cursor-not-allowed opacity-75;
        }

        .feature-card {
            @apply bg-gray-800/70 border border-gray-700 rounded-xl p-6 text-center hover:border-cyan-500 transition duration-300;
        }

        .how-it-works-step {
            @apply bg-gray-800/70 border border-gray-700 rounded-xl p-6 text-center;
        }

        /* Custom scrollbar for pre blocks */
        .prose pre::-webkit-scrollbar {
            height: 8px;
        }

        .prose pre::-webkit-scrollbar-track {
            background: #333;
            border-radius: 10px;
        }

        .prose pre::-webkit-scrollbar-thumb {
            background: #00bcd4;
            /* Cyan color */
            border-radius: 10px;
        }

        .prose pre::-webkit-scrollbar-thumb:hover {
            background: #00a0b2;
        }
    </style>
@endsection

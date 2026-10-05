@extends('layouts.app')

@section('title', $service->title)

@section('content')
    <div class="min-h-screen bg-[#050505] text-gray-100 font-sans" x-data="{ openDocs: false }">
        <!-- Hero Section -->
        <section class="relative py-20 md:py-32 px-6 lg:px-8 overflow-hidden bg-[#050505] mesh-gradient">
            <div class="relative max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-12">
                <div class="flex-1 text-center md:text-left">
                    @php
                        $categoryStyles = [
                            'Blue Team' => 'bg-blue-500/10 border-blue-400/30 text-blue-300',
                            'Red Team' => 'bg-red-500/10 border-red-400/30 text-red-300',
                            'Cloud Security' => 'bg-sky-500/10 border-sky-400/30 text-sky-300',
                        ];
                        $badgeStyle = $categoryStyles[$service->category] ?? 'bg-cyan-500/10 border-cyan-400/30 text-cyan-300';
                    @endphp

                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 mb-5">
                        <span
                            class="inline-flex items-center gap-1.5 border {{ $badgeStyle }} px-4 py-1.5 rounded-full text-[11px] font-bold uppercase tracking-widest">
                            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                            {{ $service->category }}
                        </span>

                        @if ($service->is_automated)
                            <span
                                class="inline-flex items-center gap-1.5 bg-emerald-500/10 border border-emerald-400/30 text-emerald-300 px-4 py-1.5 rounded-full text-[11px] font-bold uppercase tracking-widest">
                                <span class="relative flex h-1.5 w-1.5">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span
                                        class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-400"></span>
                                </span>
                                Automated Agent Service
                            </span>
                        @endif

                        <span class="bg-cyan-600 text-white text-xs font-bold px-3 py-1.5 rounded-full">
                            @if ($service->is_available)
                                Available Now
                            @else
                                Coming Soon
                            @endif
                        </span>
                    </div>

                    <h1 class="text-5xl md:text-6xl font-extrabold leading-tight mb-4 text-white">
                        {{ $service->title }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-4 mt-8">
                        @if ($service->requiresPayment())
                            <div class="bg-[#0a0a0a] border border-cyan-500/30 rounded-2xl px-6 py-4">
                                <div class="flex items-baseline gap-2">
                                    <span
                                        class="text-3xl font-mono font-black text-white">{{ number_format((float) $service->price, 0) }}</span>
                                    <span class="text-sm font-medium text-gray-500 uppercase">SYP</span>
                                </div>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <span class="text-sm font-mono font-bold text-cyan-400">${{ number_format($service->usdPrice(), 0) }}</span>
                                    <span class="text-[10px] text-gray-500 uppercase tracking-widest">USD equivalent / month</span>
                                </div>
                            </div>
                        @else
                            <div class="bg-[#0a0a0a] border border-cyan-500/30 rounded-2xl px-6 py-4">
                                <span class="text-2xl font-mono font-bold text-gray-300 italic">Custom Quote</span>
                            </div>
                        @endif
                    </div>

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
                                <a href="{{ route('my-tools.index') }}"
                                    class="px-8 py-4 bg-transparent border border-cyan-500/30 text-cyan-400 hover:text-white hover:bg-cyan-500/10 font-bold rounded-xl transition-all">
                                    Deployment Commands
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

                <div class="p-8 overflow-y-auto custom-scrollbar prose prose-invert max-w-none service-docs">
                        {{-- `full_description` is sanitized on write by the Service
                             model's strip_tags() allow-list, so it is safe to
                             render as HTML here to preserve the seeded structure. --}}
                        {!! $service->full_description ?: '<p class="text-gray-400">No detailed documentation available yet.</p>' !!}
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
                <h2 class="text-4xl font-bold mb-4 text-white">How It Works</h2>
                <p class="text-gray-400 max-w-2xl mx-auto mb-12">
                    From purchase to first agent heartbeat in four steps. No infrastructure to
                    provision, no console to install &mdash; just download and run.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="how-it-works-step text-left">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="text-5xl text-cyan-400 font-black">1.</div>
                            <span class="text-[10px] font-mono uppercase tracking-widest text-gray-500">Purchase</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Purchase Service</h3>
                        <p class="text-gray-400 mb-4">Select the agent you need and pay with Sham Cash or
                            bank transfer.</p>
                        <p class="text-xs font-mono text-cyan-400">
                            {{ number_format((float) $service->price, 0) }} SYP / month
                        </p>
                    </div>
                    <div class="how-it-works-step text-left">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="text-5xl text-cyan-400 font-black">2.</div>
                            <span class="text-[10px] font-mono uppercase tracking-widest text-gray-500">Activation</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Approval &amp; License</h3>
                        <p class="text-gray-400 mb-4">Our team verifies your receipt and issues a license key
                            valid for 30 days.</p>
                        <p class="text-xs font-mono text-cyan-400">Issued in /my-tools</p>
                    </div>
                    <div class="how-it-works-step text-left">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="text-5xl text-cyan-400 font-black">3.</div>
                            <span class="text-[10px] font-mono uppercase tracking-widest text-gray-500">Download</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Download Agent</h3>
                        <p class="text-gray-400 mb-4">Download your personalised
                            <code class="text-cyan-400">agent_bootstrapper.py</code> with the service ID and
                            license key pre-configured.</p>
                        <p class="text-xs font-mono text-cyan-400">One file, cross-platform</p>
                    </div>
                    <div class="how-it-works-step text-left">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="text-5xl text-cyan-400 font-black">4.</div>
                            <span class="text-[10px] font-mono uppercase tracking-widest text-gray-500">Run</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Run &amp; Monitor</h3>
                        <p class="text-gray-400 mb-4">Run one command. The agent fetches its workload,
                            executes it and reports back every 60 seconds.</p>
                        <p class="text-xs font-mono text-cyan-400">python agent_bootstrapper.py</p>
                    </div>
                </div>
            </div>
        </section>

    <!-- Installation Guide -->
        <section class="py-16 px-6 lg:px-8 bg-gray-900/60">
            <div class="max-w-5xl mx-auto">
                <h2 class="text-3xl font-bold mb-3 text-white">Installation Guide</h2>
                <p class="text-gray-400 mb-10">
                    Deploy the {{ $service->title }} on the host you want to protect.
                    These commands work on Linux, macOS and Windows.
                </p>

                @auth
                    @if ($canDownload ?? false)
                        <div class="space-y-6">
                            <div class="install-step">
                                <div class="install-step-number">1</div>
                                <div>
                                    <h3 class="text-lg font-semibold mb-2">Download the bootstrapper</h3>
                                    <p class="text-gray-400 mb-4">
                                        Open <a href="{{ route('my-tools.index') }}" class="text-cyan-400 hover:underline">My Tools</a>
                                        and download your personalised
                                        <code class="text-cyan-400">agent_bootstrapper.py</code>, or use the
                                        direct link below (replace the key with your license).
                                    </p>
                                    <code class="code-block">{{ $baseUrl ?? url('/') }}/my-tools/download-agent/{{ $service->id }}/YOUR-LICENSE-KEY</code>
                                </div>
                            </div>

                            <div class="install-step">
                                <div class="install-step-number">2</div>
                                <div>
                                    <h3 class="text-lg font-semibold mb-2">Run on Linux or macOS</h3>
                                    <code class="code-block">curl -fsSL "{{ $baseUrl ?? url('/') }}/my-tools/download-agent/{{ $service->id }}/YOUR-LICENSE-KEY" -o agent_bootstrapper.py &amp;&amp; python3 agent_bootstrapper.py</code>
                                </div>
                            </div>

                            <div class="install-step">
                                <div class="install-step-number">3</div>
                                <div>
                                    <h3 class="text-lg font-semibold mb-2">Or run on Windows</h3>
                                    <code class="code-block">Invoke-WebRequest -Uri "{{ $baseUrl ?? url('/') }}/my-tools/download-agent/{{ $service->id }}/YOUR-LICENSE-KEY" -OutFile agent_bootstrapper.py; python agent_bootstrapper.py</code>
                                </div>
                            </div>

                            <div class="install-step">
                                <div class="install-step-number">4</div>
                                <div>
                                    <h3 class="text-lg font-semibold mb-2">Watch it come online</h3>
                                    <p class="text-gray-400">
                                        The agent verifies its dependencies, fetches your licensed workload,
                                        executes it and sends a heartbeat every 60 seconds. Your agent turns
                                        <span class="text-emerald-400 font-semibold">online</span> in the
                                        CyberLogia command center immediately.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-[#0a0a0a] border border-cyan-500/20 rounded-2xl p-8 text-center">
                            <p class="text-gray-400 mb-6">
                                Purchase this service to unlock the installation guide and download your
                                personalised agent.
                            </p>
                            @if ($service->is_available)
                                <a href="{{ route('services.pay', $service->slug) }}"
                                    class="btn-primary-cyan inline-block">Purchase &amp; Unlock Agent</a>
                            @else
                                <button disabled class="btn-disabled">Service Locked</button>
                            @endif
                        </div>
                    @endif
                @else
                    <div class="bg-[#0a0a0a] border border-cyan-500/20 rounded-2xl p-10 text-center">
                        <h3 class="text-2xl font-bold mb-3 text-white">Ready to deploy this agent?</h3>
                        <p class="text-gray-400 mb-8">
                            Create your free CyberLogia account to purchase this service and receive your
                            personalised installation guide.
                        </p>
                        <div class="flex flex-wrap justify-center gap-4">
                            <a href="{{ route('register') }}" class="btn-primary-cyan">Create Account</a>
                            <a href="{{ route('login') }}" class="btn-secondary-gray">Login</a>
                        </div>
                    </div>
                @endauth
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

        .install-step {
            @apply bg-[#0a0a0a] border border-white/5 rounded-2xl p-6 flex items-start gap-5;
        }

        .install-step-number {
            @apply flex-shrink-0 w-9 h-9 rounded-full bg-cyan-500/15 border border-cyan-400/30 text-cyan-300 font-mono font-bold flex items-center justify-center;
        }

        .code-block {
            @apply block bg-black/80 border border-cyan-500/20 p-4 rounded-xl text-[11px] text-green-400 font-mono break-all whitespace-pre-wrap;
        }

        .service-docs h3 {
            @apply text-xl font-bold text-white mt-6 mb-3;
        }

        .service-docs p {
            @apply text-gray-300 leading-relaxed mb-4;
        }

        .service-docs ul {
            @apply list-disc list-inside text-gray-300 leading-relaxed mb-4 space-y-1;
        }

        .service-docs li {
            @apply marker:text-cyan-400;
        }

        .service-docs strong {
            @apply text-white font-semibold;
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

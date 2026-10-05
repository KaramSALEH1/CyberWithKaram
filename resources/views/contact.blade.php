@extends('layouts.app')
@section('title', 'Contact CyberLogia')

@section('content')
    @php
        $channels = [
            [
                '📧',
                'Email',
                'mailto:karam.saleh.cs@gmail.com',
                'karam.saleh.cs@gmail.com',
                'Best for proposals, invoices and technical questions.',
            ],
            [
                '📸',
                'Instagram',
                'https://instagram.com/cyberlogia_',
                'cyberlogia_',
                'Product updates and quick questions.',
            ],
            [
                '🤖',
                'Telegram',
                null,
                'Operations channel',
                'Fastest response for active deployments and licence issues.',
            ],
            [
                '🛡️',
                'Enterprise',
                route('services'),
                'Browse the catalogue',
                'Pricing in SYP, with tailored scoping for banks and enterprises.',
            ],
        ];
    @endphp

    <div class="min-h-screen bg-[#050505] text-gray-100">
        <section class="relative py-24 px-6 lg:px-8 overflow-hidden">
            <div class="absolute -top-32 -left-32 w-[30rem] h-[30rem] rounded-full bg-karam-green/5 blur-3xl"></div>

            <div class="max-w-7xl mx-auto relative">
                <div class="text-center mb-16">
                    <span class="inline-block font-mono text-[11px] uppercase tracking-[0.35em] text-karam-green mb-5">
                        Get in touch
                    </span>
                    <h1 class="text-4xl md:text-6xl font-black tracking-tight mb-4">
                        Talk <span class="text-karam-green">security</span> with us
                    </h1>
                    <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                        Questions about an agent, a deployment, or an enterprise agreement?
                        Our team replies within one business day.
                    </p>
                </div>

                @if (session('status'))
                    <div
                        class="max-w-2xl mx-auto mb-8 rounded-xl border border-karam-green/30 bg-karam-green/10 p-4 text-center">
                        <p class="text-karam-green text-sm font-medium">{{ session('status') }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                    <!-- Support channels -->
                    <div class="lg:col-span-2 space-y-4">
                        @foreach ($channels as [$icon, $label, $href, $value, $hint])
                            <div
                                class="group bg-[#0a0a0a] border border-white/5 hover:border-karam-green/40 rounded-2xl p-5 flex items-start gap-4 transition-all duration-300">
                                <span
                                    class="flex-shrink-0 w-11 h-11 rounded-xl bg-karam-green/10 border border-karam-green/20 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                                    {{ $icon }}
                                </span>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-mono text-gray-500 uppercase tracking-widest">
                                        {{ $label }}</p>
                                    @if ($href)
                                        <a href="{{ $href }}"
                                            class="block font-semibold text-karam-green hover:underline break-all">{{ $value }}</a>
                                    @else
                                        <p class="font-semibold text-white">{{ $value }}</p>
                                    @endif
                                    <p class="text-gray-500 text-xs mt-1 leading-relaxed">{{ $hint }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <!-- Contact form -->
                    <div class="lg:col-span-3">
                        <div class="bg-[#0a0a0a] border border-white/5 rounded-2xl p-8">
                            <h2 class="text-xl font-bold mb-1">Send an enquiry</h2>
                            <p class="text-gray-500 text-sm mb-6">
                                Fields marked <span class="text-red-400">*</span> are required.
                            </p>

                            <form method="POST" action="{{ route('contact.submit') }}" class="space-y-5">
                                @csrf

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div>
                                        <label for="contact-name"
                                            class="block text-xs font-medium text-gray-400 uppercase mb-2">
                                            Full name <span class="text-red-400">*</span>
                                        </label>
                                        <input id="contact-name" type="text" name="name" required
                                            value="{{ old('name') }}" placeholder="Jane Doe"
                                            class="w-full bg-gray-950 border border-gray-800 rounded-lg p-3.5 text-sm focus:border-karam-green outline-none transition placeholder-gray-600">
                                        @error('name')
                                            <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="contact-email"
                                            class="block text-xs font-medium text-gray-400 uppercase mb-2">
                                            Email <span class="text-red-400">*</span>
                                        </label>
                                        <input id="contact-email" type="email" name="email" required
                                            value="{{ old('email') }}" placeholder="you@company.com"
                                            class="w-full bg-gray-950 border border-gray-800 rounded-lg p-3.5 text-sm focus:border-karam-green outline-none transition placeholder-gray-600">
                                        @error('email')
                                            <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="contact-subject"
                                        class="block text-xs font-medium text-gray-400 uppercase mb-2">
                                        Subject <span class="text-red-400">*</span>
                                    </label>
                                    <input id="contact-subject" type="text" name="subject" required
                                        value="{{ old('subject') }}" placeholder="Which agent are you interested in?"
                                        class="w-full bg-gray-950 border border-gray-800 rounded-lg p-3.5 text-sm focus:border-karam-green outline-none transition placeholder-gray-600">
                                    @error('subject')
                                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="contact-message"
                                        class="block text-xs font-medium text-gray-400 uppercase mb-2">
                                        Message <span class="text-red-400">*</span>
                                    </label>
                                    <textarea id="contact-message" name="message" rows="6" required
                                        placeholder="Tell us about your environment and what you need to protect…"
                                        class="w-full bg-gray-950 border border-gray-800 rounded-lg p-3.5 text-sm focus:border-karam-green outline-none transition placeholder-gray-600 resize-y">{{ old('message') }}</textarea>
                                    @error('message')
                                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit"
                                    class="w-full bg-karam-green text-black font-bold py-4 rounded-lg hover:opacity-90 transition shadow-[0_0_30px_rgba(0,242,96,0.2)]">
                                    Send enquiry
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

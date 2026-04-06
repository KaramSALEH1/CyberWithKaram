@extends('layouts.app')

@section('title', 'Professional Cybersecurity Services')

@section('content')
    <header class="py-24 px-6 text-center max-w-4xl mx-auto flex flex-col items-center">
        <span class="text-karam-green font-semibold tracking-widest uppercase text-xs">
            Secure Today, Protect Tomorrow
        </span>
        <h1 class="text-6xl font-black mt-4 mb-6 leading-tight">
            Protect Your Digital Infrastructure With <span class="text-karam-green">Trust</span>
        </h1>
        <p class="text-gray-400 text-xl mb-10 leading-relaxed">
            Professional Penetration Testing, Cloud Defense, and Automated Security Solutions by KARAM.
        </p>
        <div class="flex flex-col md:flex-row justify-center gap-4">
            <a href="{{ route('services') }}"
                class="bg-karam-green hover:opacity-90 px-10 py-4 rounded-md font-bold text-lg transition shadow-lg shadow-karam-green/20">
                Explore Services
            </a>
            <a href="{{ route('contact') }}"
                class="border border-gray-700 hover:border-karam-green px-10 py-4 rounded-md font-bold text-lg transition">
                Request Audit
            </a>
        </div>
    </header>

    <section id="services" class="py-24 bg-gray-800/50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Our Core Expertise</h2>
                <p class="text-gray-400">High-end security services to stay ahead of cyber threats.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach ($services as $service)
                    <div
                        class="bg-gray-900 p-10 border border-gray-800 rounded-xl hover:border-karam-green transition-all duration-300 group">
                        <div class="text-4xl mb-6 text-karam-green group-hover:scale-110 transition-transform">
                            {{ $service->icon }}
                        </div>
                        <h3 class="text-xl font-bold mb-4">{{ $service->title }}</h3>
                        <p class="text-gray-400 leading-relaxed mb-6">{{ $service->description }}</p>
                        <a href="{{ route('services') }}" class="text-sm font-bold text-karam-green hover:underline">
                            Learn More →
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="contact" class="py-24 max-w-4xl mx-auto px-6">
        <div class="bg-gray-800 p-10 rounded-2xl border border-gray-700 text-center">
            <h2 class="text-3xl font-bold mb-4">Ready to secure your business?</h2>
            <p class="text-gray-400 mb-8">Contact us today for a comprehensive security assessment and vulnerability report.
            </p>
            <a href="{{ route('contact') }}"
                class="inline-block bg-karam-green hover:opacity-90 px-12 py-4 rounded-lg font-bold text-lg transition shadow-lg shadow-karam-green/20">
                Get in Touch
            </a>
        </div>
    </section>
@endsection

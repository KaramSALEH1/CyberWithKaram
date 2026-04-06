@extends('layouts.app')
@section('title', 'Our Services')

@section('content')
    <section class="py-24 px-6 max-w-7xl mx-auto">
        <div class="text-center mb-20">
            <h1 class="text-5xl font-black mb-4">Elite <span class="text-karam-green">Security</span> Services</h1>
            <p class="text-gray-400 max-w-2xl mx-auto">Providing high-end technical audits and defensive strategies for your
                infrastructure.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach ($services as $service)
                <div
                    class="bg-gray-800/50 p-10 border border-gray-800 rounded-2xl hover:border-karam-green transition duration-300">
                    <div class="text-5xl mb-6">{{ $service->icon }}</div>
                    <h3 class="text-2xl font-bold mb-4">{{ $service->title }}</h3>
                    <p class="text-gray-400 leading-relaxed mb-6">{{ $service->description }}</p>
                    <a href="{{ route('contact') }}" class="text-karam-green font-bold hover:underline">Get Started →</a>
                </div>
            @endforeach
        </div>
    </section>
@endsection

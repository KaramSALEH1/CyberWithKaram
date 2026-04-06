@extends('layouts.app')

@section('title', $service->title)

@section('content')
<section class="py-16 px-6 max-w-6xl mx-auto">
    <div class="bg-gray-800/50 border border-gray-700 rounded-2xl p-8 md:p-12">
        <div class="flex items-center gap-4 mb-6">
            @if($service->logo_url)
                <img src="{{ $service->logo_url }}" alt="{{ $service->title }}" class="h-16 object-contain">
            @else
                <span class="text-5xl">{{ $service->icon ?? '🛡️' }}</span>
            @endif
            <div>
                <p class="text-karam-green text-xs uppercase tracking-widest">{{ $service->category }}</p>
                <h1 class="text-4xl font-black">{{ $service->title }}</h1>
            </div>
        </div>

        <p class="text-gray-300 text-lg mb-8">{{ $service->description }}</p>

        <article class="prose prose-invert max-w-none">
            {!! nl2br(e($service->full_description ?: 'More details will be available soon.')) !!}
        </article>

        <div class="mt-10">
            <a href="{{ route('contact') }}" class="bg-karam-green text-black px-6 py-3 rounded-lg font-bold">Request This Service</a>
        </div>
    </div>
</section>
@endsection

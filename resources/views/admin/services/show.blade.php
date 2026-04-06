@extends('layouts.app')

@section('title', 'Service Detail Control')

@section('content')
<div class="py-10 bg-gray-900 min-h-screen text-white">
    <div class="max-w-6xl mx-auto px-6 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black">Service Detail <span class="text-karam-green">Control</span></h1>
                <p class="text-gray-400 text-sm mt-1">{{ $service->title }} | {{ $service->slug }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.services.edit', $service) }}" class="bg-blue-700 px-4 py-2 rounded-lg font-bold">Edit</a>
                <a href="{{ route('admin.services.index') }}" class="bg-gray-700 px-4 py-2 rounded-lg font-bold">All Services</a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-700 bg-green-900/20 p-4 text-green-300">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 space-y-3">
                <h2 class="text-lg font-bold text-karam-green">Service Profile</h2>
                <p><span class="text-gray-400">Name:</span> {{ $service->title }}</p>
                <p><span class="text-gray-400">Category:</span> {{ $service->category }}</p>
                <p><span class="text-gray-400">Visibility:</span> {{ $service->is_visible ? 'Visible' : 'Hidden' }}</p>
                <p><span class="text-gray-400">Automation:</span> {{ $service->is_automated ? 'Enabled' : 'Manual' }}</p>
                @if($service->logo_url)
                    <img src="{{ $service->logo_url }}" alt="{{ $service->title }}" class="mt-2 h-14 object-contain rounded">
                @endif
            </div>

            <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 lg:col-span-2 space-y-4">
                <h2 class="text-lg font-bold text-karam-green">Descriptions</h2>
                <p class="text-gray-200"><span class="text-gray-400">Short:</span> {{ $service->description }}</p>
                <p class="text-gray-300 whitespace-pre-line">{{ $service->full_description ?: 'No full description yet.' }}</p>
            </div>
        </div>

        <div class="bg-gray-800 border border-gray-700 rounded-xl p-5">
            <h2 class="text-lg font-bold text-karam-green mb-3">Agent Payload Script</h2>
            <pre class="bg-gray-950 border border-gray-700 rounded-lg p-4 text-xs overflow-x-auto text-green-300">{{ $service->script_code ?: '# No payload set yet' }}</pre>
        </div>
    </div>
</div>
@endsection

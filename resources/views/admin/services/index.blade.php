@extends('layouts.app')

@section('title', 'Service Control Center')

@section('content')
<div class="py-10 bg-gray-900 min-h-screen text-white">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-black">Service <span class="text-karam-green">Control Center</span></h1>
                <p class="text-gray-400 text-sm mt-1">Manage service content, payload scripts, and visibility.</p>
            </div>
            <a href="{{ route('admin.services.create') }}" class="bg-karam-green text-black px-5 py-2 rounded-lg font-bold">+ New Service</a>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-green-700 bg-green-900/20 p-4 text-green-300">{{ session('success') }}</div>
        @endif

        <div class="space-y-3">
            @forelse($services as $service)
                <div class="bg-gray-800 border border-gray-700 rounded-xl p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="text-2xl">{{ $service->icon ?: '🛡️' }}</div>
                        <div>
                            <p class="font-bold">{{ $service->title }}</p>
                            <p class="text-xs text-gray-400">{{ $service->slug }} | {{ $service->category }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs px-2 py-1 rounded {{ $service->is_visible ? 'bg-green-900 text-green-300' : 'bg-red-900 text-red-300' }}">{{ $service->is_visible ? 'Visible' : 'Hidden' }}</span>
                        <a href="{{ route('admin.services.show', $service) }}" class="px-3 py-2 rounded bg-gray-700 text-xs font-bold">Control</a>
                        <a href="{{ route('admin.services.edit', $service) }}" class="px-3 py-2 rounded bg-blue-900 text-blue-300 text-xs font-bold">Edit</a>
                        <form method="POST" action="{{ route('admin.services.destroy', $service) }}" onsubmit="return confirm('Delete this service?')">
                            @csrf
                            @method('DELETE')
                            <button class="px-3 py-2 rounded bg-red-900 text-red-300 text-xs font-bold">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 text-gray-400">No services yet.</div>
            @endforelse
        </div>

        <div class="mt-6">{{ $services->links() }}</div>
    </div>
</div>
@endsection

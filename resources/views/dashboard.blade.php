@extends('layouts.app')

@section('content')
    <div class="py-12 bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto px-6">
            <h1 class="text-3xl font-black mb-10">CyberWithKaram <span class="text-karam-green">Command Center</span></h1>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <aside class="lg:col-span-1 bg-gray-800 p-6 rounded-2xl border border-gray-700 shadow-xl h-fit">
                    <h2 class="text-lg font-bold text-karam-green mb-4">Admin Navigation</h2>
                    <nav class="space-y-2 text-sm">
                        <a href="{{ route('admin.services.index') }}" class="block bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 hover:border-karam-green">Service Management</a>
                        <a href="{{ route('admin.academy.index') }}" class="block bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 hover:border-karam-green">Academy Management</a>
                        <a href="{{ route('admin.command-center.index') }}" class="block bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 hover:border-karam-green">Agent Command Center</a>
                    </nav>
                </aside>

                <div class="bg-gray-800 p-8 rounded-2xl border border-gray-700 shadow-xl lg:col-span-3">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-karam-green">Services Snapshot</h2>
                        <span class="text-xs text-gray-500">{{ \App\Models\Service::count() }} Total</span>
                    </div>

                    <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                        @foreach ($services as $service)
                            <div
                                class="bg-gray-900 p-4 rounded-xl border border-gray-800 flex justify-between items-center">
                                <div>
                                    <p class="font-bold text-white">{{ $service->title }}</p>
                                    <p class="text-xs text-gray-500">{{ $service->category }} |
                                        {{ $service->is_automated ? 'Automated' : 'Manual' }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <span class="px-3 py-1 rounded text-xs font-bold {{ $service->is_visible ? 'bg-green-900 text-green-400' : 'bg-red-900 text-red-400' }}">{{ $service->is_visible ? 'Visible' : 'Hidden' }}</span>
                                    <a href="{{ route('admin.services.show', $service) }}" class="px-3 py-1 rounded text-xs font-bold bg-gray-700">Control</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-5">
                        <a href="{{ route('admin.services.index') }}" class="text-karam-green font-bold hover:underline">Open full Service Management -></a>
                    </div>
                </div>
                <div class="bg-gray-800 p-8 rounded-2xl border border-gray-700 shadow-xl lg:col-span-4">
                    <h2 class="text-xl font-bold text-blue-400 mb-6">Add New Course Lesson</h2>
                    <form action="{{ route('admin.dashboard.lesson.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Lesson Title</label>
                            <input type="text" name="title"
                                class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 outline-none focus:border-karam-green transition"
                                placeholder="e.g. Intro to Nmap">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">YouTube URL</label>
                            <input type="text" name="youtube_url"
                                class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 outline-none focus:border-karam-green transition"
                                placeholder="https://www.youtube.com/watch?v=...">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Lesson Description</label>
                            <textarea name="description" rows="3"
                                class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 outline-none focus:border-karam-green transition"></textarea>
                        </div>
                        <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 py-3 rounded-lg font-black transition">PUBLISH
                            LESSON</button>
                    </form>

                    <div class="mt-8">
                        <h3 class="text-sm font-bold text-gray-400 mb-4 uppercase italic">Active Playlist</h3>
                        <div class="space-y-2">
                            @foreach ($lessons as $lesson)
                                <div class="flex items-center gap-3 bg-gray-900/50 p-2 rounded">
                                    <span class="text-karam-green text-xs font-mono">#{{ $lesson->order_no }}</span>
                                    <span class="text-sm">{{ $lesson->title }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="py-12 bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto px-6">
            <h1 class="text-3xl font-black mb-10">CyberWithKaram <span class="text-karam-green">Command Center</span></h1>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

                <div class="bg-gray-800 p-8 rounded-2xl border border-gray-700 shadow-xl">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-karam-green">Manage Services</h2>
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
                                    <form action="{{ route('admin.services.toggle', $service->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="px-3 py-1 rounded text-xs font-bold {{ $service->is_visible ? 'bg-green-900 text-green-400' : 'bg-red-900 text-red-400' }}">
                                            {{ $service->is_visible ? 'Visible' : 'Hidden' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-gray-800 p-8 rounded-2xl border border-gray-700 shadow-xl">
                    <h2 class="text-xl font-bold text-blue-400 mb-6">Add New Course Lesson</h2>
                    <form action="{{ route('admin.lessons.store') }}" method="POST" class="space-y-4">
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

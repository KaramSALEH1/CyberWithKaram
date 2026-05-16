@extends('layouts.admin')

@section('title', $course->title.' Management')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black">{{ $course->title }}</h1>
            <p class="text-gray-400 text-sm">{{ $course->slug }} | {{ $course->level }}</p>
        </div>
        <a href="{{ route('admin.academy.index') }}" class="bg-gray-700 px-4 py-2 rounded-lg font-bold text-sm">Back to Academy</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <form action="{{ route('admin.module.store') }}" method="POST" class="bg-gray-900 border border-gray-800 rounded-xl p-5 space-y-3">
            @csrf
            <input type="hidden" name="course_id" value="{{ $course->id }}">
            <h2 class="font-bold text-blue-400">Add Module</h2>
            <input type="text" name="title" placeholder="Module title" class="w-full bg-gray-950 border border-gray-700 rounded-lg p-3" required>
            <label class="flex items-center gap-2 text-sm text-gray-300">
                <input type="checkbox" name="requires_purchase" value="1" class="rounded border-gray-600 bg-gray-900">
                Requires purchase
            </label>
            <button class="bg-blue-700 px-4 py-2 rounded-lg font-bold text-sm">Add Module</button>
        </form>

        @foreach($course->modules as $module)
            <form action="{{ route('admin.lesson.store') }}" method="POST" class="bg-gray-900 border border-gray-800 rounded-xl p-5 space-y-3">
                @csrf
                <input type="hidden" name="module_id" value="{{ $module->id }}">
                <h2 class="font-bold text-yellow-500">Add Lesson to: {{ $module->title }}</h2>
                <input type="text" name="title" placeholder="Lesson title" class="w-full bg-gray-950 border border-gray-700 rounded-lg p-3" required>
                <input type="text" name="video_url" placeholder="YouTube video ID" class="w-full bg-gray-950 border border-gray-700 rounded-lg p-3" required>
                <textarea name="content" rows="3" placeholder="Lesson content" class="w-full bg-gray-950 border border-gray-700 rounded-lg p-3"></textarea>
                <label class="flex items-center gap-2 text-sm text-gray-300">
                    <input type="checkbox" name="requires_purchase" value="1" class="rounded border-gray-600 bg-gray-900">
                    Requires purchase
                </label>
                <button class="bg-yellow-700 px-4 py-2 rounded-lg font-bold text-sm">Publish Lesson</button>
            </form>
        @endforeach
    </div>

    <div class="space-y-4">
        @foreach($course->modules as $module)
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-lg font-bold text-karam-green">{{ $module->title }}</h3>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.module.edit', $module) }}" class="text-xs bg-amber-600/20 text-amber-400 border border-amber-600/50 px-3 py-1 rounded-md hover:bg-amber-600/40 transition">Edit</a>
                        <form action="{{ route('admin.module.delete', $module) }}" method="POST" onsubmit="return confirm('Delete this module and all its lessons?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs bg-red-900/40 text-red-400 border border-red-900 px-3 py-1 rounded-md hover:bg-red-900/60 transition">Delete</button>
                        </form>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @forelse($module->lessons as $lesson)
                        <div class="bg-gray-950 border border-gray-800 rounded-lg p-3 text-sm flex justify-between items-center group">
                            <div>
                                <span class="text-karam-green font-mono text-xs">#{{ $lesson->order_no }}</span>
                                {{ $lesson->title }}
                            </div>
                            <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition">
                                <a href="{{ route('admin.lesson.edit', $lesson) }}" class="text-[10px] bg-amber-600/20 text-amber-400 border border-amber-600/50 px-2 py-0.5 rounded hover:bg-amber-600/40 transition">Edit</a>
                                <form action="{{ route('admin.lesson.delete', $lesson) }}" method="POST" onsubmit="return confirm('Delete this lesson?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-[10px] bg-red-900/40 text-red-400 border border-red-900 px-2 py-0.5 rounded hover:bg-red-900/60 transition">Delete</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">No lessons in this module.</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection



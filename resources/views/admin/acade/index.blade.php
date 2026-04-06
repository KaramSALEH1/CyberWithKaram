@extends('layouts.app')
@section('content')
<div class="py-12 bg-gray-900 min-h-screen text-white">
    <div class="max-w-6xl mx-auto px-6">
        <div class="flex justify-between items-center mb-10">
            <h1 class="text-3xl font-black italic text-karam-green underline">ACADEMY COMMAND CENTER</h1>
            <button onclick="document.getElementById('courseModal').classList.remove('hidden')" class="bg-karam-green px-6 py-2 rounded-lg font-bold shadow-lg hover:scale-105 transition">+ Create New Course</button>
        </div>

        <div class="space-y-8">
            @foreach($courses as $course)
            <div class="bg-gray-800 border border-gray-700 rounded-2xl overflow-hidden shadow-2xl hover:border-karam-green/50 transition">
                <div class="p-6 bg-gray-700/50 flex justify-between items-center border-b border-gray-600">
                    <div>
                        <span class="text-xs font-bold text-karam-green uppercase tracking-widest">{{ $course->level }}</span>
                        <h2 class="text-2xl font-black">{{ $course->title }}</h2>
                    </div>
                    <button onclick="document.getElementById('course_id_input').value='{{ $course->id }}'; document.getElementById('moduleModal').classList.remove('hidden')" class="text-sm bg-gray-600 hover:bg-karam-green px-4 py-2 rounded-md transition">+ Add Module</button>
                </div>

                <div class="p-6 space-y-6">
                    @forelse($course->modules as $module)
                    <div class="bg-gray-900/50 p-4 rounded-xl border-l-4 border-karam-green">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-lg text-gray-200 uppercase">{{ $module->title }}</h3>
                            <button onclick="document.getElementById('module_id_input').value='{{ $module->id }}'; document.getElementById('lessonModal').classList.remove('hidden')" class="text-xs bg-blue-900/30 text-blue-400 border border-blue-900 px-3 py-1 rounded-full hover:bg-blue-900 transition">+ Add Lesson</button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($module->lessons as $lesson)
                            <div class="bg-gray-800 p-3 rounded-lg flex items-center gap-3 border border-gray-700 group hover:border-karam-green transition">
                                <span class="text-karam-green font-mono text-xs">{{ $loop->iteration }}</span>
                                <span class="text-sm font-medium">{{ $lesson->title }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <p class="text-gray-500 italic text-center py-4 text-sm">No modules yet.</p>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div id="courseModal" class="hidden fixed inset-0 bg-black/90 flex items-center justify-center z-50 p-4">
    <div class="bg-gray-800 p-8 rounded-2xl w-full max-w-md border border-gray-700">
        <h2 class="text-xl font-bold mb-6 text-karam-green">New Course</h2>
        <form action="{{ route('admin.course.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="text" name="title" placeholder="Course Title" class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white outline-none focus:border-karam-green" required>
            <select name="level" class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white">
                <option value="Beginner">Beginner</option>
                <option value="Intermediate">Intermediate</option>
                <option value="Advanced">Advanced</option>
            </select>
            <textarea name="description" placeholder="Summary" class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white h-24"></textarea>
            <label class="flex items-center gap-2 text-xs text-gray-300">
                <input type="checkbox" name="requires_purchase" value="1" class="rounded border-gray-600 bg-gray-900">
                Require payment for this course
            </label>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-karam-green py-3 rounded-lg font-bold">Save</button>
                <button type="button" onclick="this.closest('#courseModal').classList.add('hidden')" class="flex-1 bg-gray-700 py-3 rounded-lg font-bold">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="moduleModal" class="hidden fixed inset-0 bg-black/90 flex items-center justify-center z-50 p-4">
    <div class="bg-gray-800 p-8 rounded-2xl w-full max-w-md border border-gray-700">
        <h2 class="text-xl font-bold mb-6 text-blue-400">Add Module</h2>
        <form action="{{ route('admin.module.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="course_id" id="course_id_input">
            <input type="text" name="title" placeholder="Module Title (e.g. Scanning Basics)" class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white outline-none focus:border-blue-400" required>
            <label class="flex items-center gap-2 text-xs text-gray-300">
                <input type="checkbox" name="requires_purchase" value="1" class="rounded border-gray-600 bg-gray-900">
                Require payment for this module
            </label>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-blue-600 py-3 rounded-lg font-bold text-white">Add Module</button>
                <button type="button" onclick="this.closest('#moduleModal').classList.add('hidden')" class="flex-1 bg-gray-700 py-3 rounded-lg font-bold">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="lessonModal" class="hidden fixed inset-0 bg-black/90 flex items-center justify-center z-50 p-4">
    <div class="bg-gray-800 p-8 rounded-2xl w-full max-w-lg border border-gray-700">
        <h2 class="text-xl font-bold mb-6 text-yellow-500">New Lesson</h2>
        <form action="{{ route('admin.lesson.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="module_id" id="module_id_input">
            <input type="text" name="title" placeholder="Lesson Title" class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white outline-none focus:border-yellow-500" required>
            <input type="text" name="video_url" placeholder="YouTube Video ID (e.g. dQw4w9WgXcQ)" class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white outline-none focus:border-yellow-500" required>
            <textarea name="content" placeholder="Technical lesson notes (HTML/Text)..." class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white h-32"></textarea>
            <label class="flex items-center gap-2 text-xs text-gray-300">
                <input type="checkbox" name="requires_purchase" value="1" class="rounded border-gray-600 bg-gray-900">
                Require payment for this lesson
            </label>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-yellow-600 py-3 rounded-lg font-bold text-white">Publish Lesson</button>
                <button type="button" onclick="this.closest('#lessonModal').classList.add('hidden')" class="flex-1 bg-gray-700 py-3 rounded-lg font-bold text-white">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection
@extends('layouts.admin')
@section('content')
    <div class="py-12 bg-gray-900 min-h-screen text-white">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex justify-between items-center mb-10">
                <h1 class="text-3xl font-black italic text-karam-green underline">ACADEMY COMMAND CENTER</h1>
                <button onclick="document.getElementById('courseModal').classList.remove('hidden')"
                    class="bg-karam-green px-6 py-2 rounded-lg font-bold shadow-lg hover:scale-105 transition">+ Create New
                    Course</button>
            </div>

            <div class="space-y-8">
                @foreach ($courses as $course)
                    <div
                        class="bg-gray-800 border border-gray-700 rounded-2xl overflow-hidden shadow-2xl hover:border-karam-green/50 transition">
                        <div class="p-6 bg-gray-700/50 flex justify-between items-center border-b border-gray-600">
                            <div>
                                <span
                                    class="text-xs font-bold text-karam-green uppercase tracking-widest">{{ $course->level }}</span>
                                <h2 class="text-2xl font-black">{{ $course->title }}</h2>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.course.show', $course) }}"
                                    class="text-sm bg-blue-900/40 text-blue-300 border border-blue-900 px-4 py-2 rounded-md font-bold">Manage</a>
                                <a href="{{ route('admin.course.edit', $course) }}"
                                    class="text-sm bg-amber-600/20 text-amber-400 border border-amber-600/50 px-4 py-2 rounded-md font-bold hover:bg-amber-600/40 transition">Edit</a>
                                <form action="{{ route('admin.course.delete', $course) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this course and all its modules/lessons?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-sm bg-red-900/40 text-red-400 border border-red-900 px-4 py-2 rounded-md font-bold hover:bg-red-900/60 transition">Delete</button>
                                </form>
                                <button
                                    onclick="document.getElementById('course_id_input').value='{{ $course->id }}'; document.getElementById('moduleModal').classList.remove('hidden')"
                                    class="text-sm bg-gray-600 hover:bg-karam-green px-4 py-2 rounded-md transition">+ Add
                                    Module</button>
                            </div>
                        </div>

                        <div class="p-6 space-y-6">
                            @forelse($course->modules as $module)
                                <div class="bg-gray-900/50 p-4 rounded-xl border-l-4 border-karam-green">
                                    <div class="flex justify-between items-center mb-4">
                                        <div class="flex flex-col">
                                            <h3 class="font-bold text-lg text-gray-200 uppercase">{{ $module->title }}</h3>
                                            @if($module->price > 0)
                                                <span class="text-[10px] text-karam-green font-bold uppercase tracking-tighter">{{ number_format($module->price, 0) }} SYP</span>
                                            @endif
                                        </div>
                                        <div class="flex gap-2">
                                            <a href="{{ route('admin.module.edit', $module) }}" class="text-[10px] bg-amber-600/20 text-amber-400 border border-amber-600/50 px-3 py-1 rounded-full hover:bg-amber-600/40 transition">Edit</a>
                                            <button
                                                onclick="document.getElementById('module_id_input').value='{{ $module->id }}'; document.getElementById('lessonModal').classList.remove('hidden')"
                                                class="text-xs bg-blue-900/30 text-blue-400 border border-blue-900 px-3 py-1 rounded-full hover:bg-blue-900 transition">+
                                                Add Lesson</button>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @foreach ($module->lessons as $lesson)
                                            <div
                                                class="bg-gray-800 p-3 rounded-lg flex items-center justify-between border border-gray-700 group hover:border-karam-green transition">
                                                <div class="flex items-center gap-3">
                                                    <span
                                                        class="text-karam-green font-mono text-xs">{{ $loop->iteration }}</span>
                                                    <div class="flex flex-col">
                                                        <span class="text-sm font-medium">{{ $lesson->title }}</span>
                                                        @if($lesson->price > 0)
                                                            <span class="text-[10px] text-karam-green font-bold uppercase">{{ number_format($lesson->price, 0) }} SYP</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition">
                                                    <a href="{{ route('admin.lesson.edit', $lesson) }}" class="text-[10px] text-amber-400 hover:underline">Edit</a>
                                                </div>
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
                <input type="text" name="title" placeholder="Course Title"
                    class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white outline-none focus:border-karam-green"
                    required>
                <select name="level" class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white">
                    <option value="Beginner">Beginner</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Advanced">Advanced</option>
                </select>
                <textarea name="description" placeholder="Summary"
                    class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white h-24"></textarea>
                <div>
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Course Price
                        (SYP)</label>
                    <input type="number" name="price" placeholder="e.g. 50000" step="0.01"
                        class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white outline-none focus:border-karam-green">
                </div>
                <label class="flex items-center gap-2 text-xs text-gray-300">
                    <input type="checkbox" name="requires_purchase" value="1"
                        class="rounded border-gray-600 bg-gray-900">
                    Require payment for this course
                </label>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-karam-green py-3 rounded-lg font-bold">Save</button>
                    <button type="button" onclick="this.closest('#courseModal').classList.add('hidden')"
                        class="flex-1 bg-gray-700 py-3 rounded-lg font-bold">Cancel</button>
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
                <input type="text" name="title" placeholder="Module Title (e.g. Scanning Basics)"
                    class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white outline-none focus:border-blue-400"
                    required>
                <div>
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Module Price
                        (Optional)</label>
                    <input type="number" name="price" placeholder="Leave empty to inherit course price" step="0.01"
                        class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white outline-none focus:border-blue-400">
                </div>
                <label class="flex items-center gap-2 text-xs text-gray-300">
                    <input type="checkbox" name="requires_purchase" value="1"
                        class="rounded border-gray-600 bg-gray-900">
                    Require payment for this module
                </label>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 py-3 rounded-lg font-bold text-white">Add
                        Module</button>
                    <button type="button" onclick="this.closest('#moduleModal').classList.add('hidden')"
                        class="flex-1 bg-gray-700 py-3 rounded-lg font-bold">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div id="lessonModal" class="hidden fixed inset-0 bg-black/90 flex items-center justify-center z-50 p-4"
        x-data="{ videoType: 'youtube' }">
        <div class="bg-gray-800 p-8 rounded-2xl w-full max-w-lg border border-gray-700">
            <h2 class="text-xl font-bold mb-6 text-yellow-500 italic">NEW LESSON</h2>
            <form action="{{ route('admin.lesson.store') }}" method="POST" enctype="multipart/form-data"
                class="space-y-4">
                @csrf
                <input type="hidden" name="module_id" id="module_id_input">

                <div>
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Lesson
                        Title</label>
                    <input type="text" name="title" placeholder="Lesson Title"
                        class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white outline-none focus:border-yellow-500"
                        required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Source</label>
                        <select name="video_type" x-model="videoType"
                            class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white outline-none focus:border-yellow-500">
                            <option value="youtube">YouTube</option>
                            <option value="local">Local MP4</option>
                        </select>
                    </div>
                    <div x-show="videoType === 'youtube'">
                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Video
                            ID</label>
                        <input type="text" name="video_url" placeholder="e.g. dQw4w9WgXcQ"
                            class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white outline-none focus:border-yellow-500">
                    </div>
                    <div x-show="videoType === 'local'">
                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">MP4
                            File</label>
                        <input type="file" name="video_file"
                            class="w-full bg-gray-900 border-gray-700 rounded-lg p-2.5 text-white outline-none focus:border-yellow-500">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Technical
                        Notes</label>
                    <textarea name="content" placeholder="Markdown supported..."
                        class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white h-32 outline-none focus:border-yellow-500"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center gap-2 text-xs text-gray-300">
                        <input type="checkbox" name="requires_purchase" value="1"
                            class="rounded border-gray-600 bg-gray-900 text-yellow-500">
                        Require purchase
                    </label>
                    <label class="flex items-center gap-2 text-xs text-gray-300">
                        <input type="checkbox" name="is_free" value="1"
                            class="rounded border-gray-600 bg-gray-900 text-green-500">
                        Free Preview
                    </label>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Lesson Price
                        (Optional)</label>
                    <input type="number" name="price" placeholder="Leave empty to inherit module price"
                        step="0.01"
                        class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white outline-none focus:border-yellow-500">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-yellow-600 py-3 rounded-lg font-bold text-white">Publish
                        Lesson</button>
                    <button type="button" onclick="this.closest('#lessonModal').classList.add('hidden')"
                        class="flex-1 bg-gray-700 py-3 rounded-lg font-bold text-white">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection

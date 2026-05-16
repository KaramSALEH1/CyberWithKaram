@extends('layouts.admin')

@section('content')
    <div class="py-12 bg-gray-900 min-h-screen text-white">
        <div class="max-w-2xl mx-auto px-6">
            <h1 class="text-3xl font-black mb-8 text-yellow-500 italic">EDIT LESSON</h1>

            <form action="{{ route('admin.lesson.update', $lesson) }}" method="POST" enctype="multipart/form-data"
                class="bg-gray-800 p-8 rounded-2xl border border-gray-700 shadow-2xl space-y-6" x-data="{ videoType: '{{ $lesson->video_type }}' }">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-bold text-gray-400 mb-2 uppercase tracking-widest">Lesson Title</label>
                    <input type="text" name="title" value="{{ $lesson->title }}"
                        class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white focus:border-yellow-500 outline-none"
                        required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-400 mb-2 uppercase tracking-widest">Video
                            Source</label>
                        <select name="video_type" x-model="videoType"
                            class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white focus:border-yellow-500 outline-none">
                            <option value="youtube">YouTube (Embed)</option>
                            <option value="local">Local Server (.mp4)</option>
                        </select>
                    </div>

                    <div x-show="videoType === 'youtube'">
                        <label class="block text-sm font-bold text-gray-400 mb-2 uppercase tracking-widest">YouTube Video
                            ID</label>
                        <input type="text" name="video_url" value="{{ $lesson->video_url }}"
                            placeholder="e.g. dQw4w9WgXcQ"
                            class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white focus:border-yellow-500 outline-none">
                    </div>

                    <div x-show="videoType === 'local'">
                        <label class="block text-sm font-bold text-gray-400 mb-2 uppercase tracking-widest">Upload Video
                            File</label>
                        <input type="file" name="video_file"
                            class="w-full bg-gray-900 border-gray-700 rounded-lg p-2.5 text-white focus:border-yellow-500 outline-none">
                        @if ($lesson->video_path)
                            <p class="text-[10px] text-karam-green mt-1">Current: {{ $lesson->video_path }}</p>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-400 mb-2 uppercase tracking-widest">Content/Notes
                        (Markdown)</label>
                    <textarea name="content"
                        class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white h-48 focus:border-yellow-500 outline-none">{{ $lesson->content }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center gap-3 text-sm text-gray-300">
                        <input type="checkbox" name="requires_purchase" value="1"
                            {{ $lesson->requires_purchase ? 'checked' : '' }}
                            class="rounded border-gray-600 bg-gray-900 text-yellow-500 focus:ring-yellow-500">
                        Require purchase
                    </label>

                    <label class="flex items-center gap-3 text-sm text-gray-300">
                        <input type="checkbox" name="is_free" value="1" {{ $lesson->is_free ? 'checked' : '' }}
                            class="rounded border-gray-600 bg-gray-900 text-green-500 focus:ring-green-500">
                        Mark as Free Preview
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-400 mb-2 uppercase tracking-widest">Lesson Price (Optional)</label>
                    <input type="number" name="price" value="{{ $lesson->price }}" step="0.01" class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white focus:border-yellow-500 outline-none" placeholder="Leave empty to inherit module price">
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="submit"
                        class="flex-1 bg-yellow-600 py-3 rounded-lg font-bold hover:scale-105 transition shadow-lg">Update
                        Lesson</button>
                    <a href="{{ route('admin.course.show', $lesson->module->course_id) }}"
                        class="flex-1 bg-gray-700 py-3 rounded-lg font-bold text-center hover:bg-gray-600 transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

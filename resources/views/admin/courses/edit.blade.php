@extends('layouts.admin')

@section('content')
    <div class="py-12 bg-gray-900 min-h-screen text-white">
        <div class="max-w-2xl mx-auto px-6">
            <h1 class="text-3xl font-black mb-8 text-karam-green italic">EDIT COURSE</h1>

            <form action="{{ route('admin.course.update', $course) }}" method="POST"
                class="bg-gray-800 p-8 rounded-2xl border border-gray-700 shadow-2xl space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-bold text-gray-400 mb-2 uppercase tracking-widest">Course Title</label>
                    <input type="text" name="title" value="{{ $course->title }}"
                        class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white focus:border-karam-green outline-none"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-400 mb-2 uppercase tracking-widest">Level</label>
                    <select name="level"
                        class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white focus:border-karam-green outline-none">
                        <option value="Beginner" {{ $course->level == 'Beginner' ? 'selected' : '' }}>Beginner</option>
                        <option value="Intermediate" {{ $course->level == 'Intermediate' ? 'selected' : '' }}>Intermediate
                        </option>
                        <option value="Advanced" {{ $course->level == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-400 mb-2 uppercase tracking-widest">Description</label>
                    <textarea name="description"
                        class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white h-32 focus:border-karam-green outline-none">{{ $course->description }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-400 mb-2 uppercase tracking-widest">Course Price
                        (SYP)</label>
                    <input type="number" name="price" value="{{ $course->price }}" step="0.01"
                        class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white focus:border-karam-green outline-none">
                </div>

                <label class="flex items-center gap-3 text-sm text-gray-300">
                    <input type="checkbox" name="requires_purchase" value="1"
                        {{ $course->requires_purchase ? 'checked' : '' }}
                        class="rounded border-gray-600 bg-gray-900 text-karam-green focus:ring-karam-green">
                    Require payment for this course
                </label>

                <div class="flex gap-4 pt-4">
                    <button type="submit"
                        class="flex-1 bg-karam-green py-3 rounded-lg font-bold hover:scale-105 transition shadow-lg">Update
                        Course</button>
                    <a href="{{ route('admin.academy.index') }}"
                        class="flex-1 bg-gray-700 py-3 rounded-lg font-bold text-center hover:bg-gray-600 transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

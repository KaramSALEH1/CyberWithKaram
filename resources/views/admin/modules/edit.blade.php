@extends('layouts.admin')

@section('content')
    <div class="py-12 bg-gray-900 min-h-screen text-white">
        <div class="max-w-2xl mx-auto px-6">
            <h1 class="text-3xl font-black mb-8 text-blue-400 italic">EDIT MODULE</h1>

            <form action="{{ route('admin.module.update', $module) }}" method="POST"
                class="bg-gray-800 p-8 rounded-2xl border border-gray-700 shadow-2xl space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-bold text-gray-400 mb-2 uppercase tracking-widest">Module Title</label>
                    <input type="text" name="title" value="{{ $module->title }}"
                        class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white focus:border-blue-400 outline-none"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-400 mb-2 uppercase tracking-widest">Module Price
                        (Optional)</label>
                    <input type="number" name="price" value="{{ $module->price }}" step="0.01"
                        class="w-full bg-gray-900 border-gray-700 rounded-lg p-3 text-white focus:border-blue-400 outline-none"
                        placeholder="Leave empty to inherit course price">
                </div>

                <label class="flex items-center gap-3 text-sm text-gray-300">
                    <input type="checkbox" name="requires_purchase" value="1"
                        {{ $module->requires_purchase ? 'checked' : '' }}
                        class="rounded border-gray-600 bg-gray-900 text-blue-400 focus:ring-blue-400">
                    Require payment for this module
                </label>

                <div class="flex gap-4 pt-4">
                    <button type="submit"
                        class="flex-1 bg-blue-600 py-3 rounded-lg font-bold hover:scale-105 transition shadow-lg">Update
                        Module</button>
                    <a href="{{ route('admin.course.show', $module->course_id) }}"
                        class="flex-1 bg-gray-700 py-3 rounded-lg font-bold text-center hover:bg-gray-600 transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

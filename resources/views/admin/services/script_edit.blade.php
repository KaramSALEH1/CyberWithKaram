@extends('layouts.admin')

@section('title', 'Edit Service Script')

@section('content')
<div class="py-10 bg-gray-900 min-h-screen text-white">
    <div class="max-w-6xl mx-auto px-6 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black">Edit Python Script <span class="text-karam-green">Payload</span></h1>
                <p class="text-gray-400 text-sm mt-1">{{ $service->title }} · {{ $service->slug }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.services.show', $service) }}" class="bg-gray-700 px-4 py-2 rounded-lg font-bold">Back to Service</a>
                <a href="{{ route('admin.services.index') }}" class="bg-gray-700 px-4 py-2 rounded-lg font-bold">All Services</a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-700 bg-green-900/20 p-4 text-green-300">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="mt-4 rounded-lg border border-red-700 bg-red-900/20 p-4 text-sm text-red-300">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-gray-800 border border-gray-700 rounded-xl p-5">
            <form action="{{ route('admin.services.script.update', $service) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs uppercase text-gray-400 block mb-1">Upload Python File (.py)</label>
                        <input type="file" name="script_file" accept=".py" class="mt-1 w-full bg-gray-950 border border-gray-700 rounded-lg p-3 text-sm text-gray-300">
                        <p class="text-xs text-gray-500 mt-1">Optional: upload a .py file to replace the script.</p>
                    </div>
                    <div>
                        <label class="text-xs uppercase text-gray-400 block mb-1">Or paste script content directly</label>
                        <div class="mt-1">
                            <textarea name="script_content" rows="24" class="w-full bg-gray-950 border border-gray-700 rounded-lg p-3 font-mono text-sm text-gray-200 focus:outline-none focus:border-karam-green">{{ old('script_content', $content) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="submit" class="bg-karam-green text-black font-bold px-6 py-3 rounded-lg">Save Script</button>
                    <a href="{{ route('admin.services.show', $service) }}" class="bg-gray-700 px-6 py-3 rounded-lg font-bold">Cancel</a>
                </div>
            </form>
        </div>

        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-5">
            <h2 class="text-lg font-bold text-karam-green mb-3">Current Stored Script</h2>
            <div class="relative">
                <pre class="bg-gray-950 border border-gray-700 rounded-lg p-4 text-xs overflow-x-auto text-gray-400 max-h-96 overflow-y-auto">{{ $content ?: '# No script stored yet. Paste or upload a script above.' }}</pre>
            </div>
            <p class="text-xs text-gray-500 mt-2">Stored privately in <code class="text-karam-green">storage/app/service_scripts/{{ $service->id }}/script.py</code> — not publicly accessible via URL.</p>
        </div>
    </div>
</div>
@endsection

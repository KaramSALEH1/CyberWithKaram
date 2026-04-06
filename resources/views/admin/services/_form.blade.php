@php
    $isEdit = isset($service);
    $service = $service ?? null;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="text-xs uppercase text-gray-400">Name</label>
        <input type="text" name="title" value="{{ old('title', $service?->title) }}" class="mt-1 w-full bg-gray-950 border border-gray-700 rounded-lg p-3" required>
    </div>
    <div>
        <label class="text-xs uppercase text-gray-400">Slug</label>
        <input type="text" name="slug" value="{{ old('slug', $service?->slug) }}" class="mt-1 w-full bg-gray-950 border border-gray-700 rounded-lg p-3" placeholder="auto-generated if empty">
    </div>
    <div>
        <label class="text-xs uppercase text-gray-400">Category</label>
        <input type="text" name="category" value="{{ old('category', $service?->category) }}" class="mt-1 w-full bg-gray-950 border border-gray-700 rounded-lg p-3" required>
    </div>
    <div>
        <label class="text-xs uppercase text-gray-400">Icon</label>
        <input type="text" name="icon" value="{{ old('icon', $service?->icon) }}" class="mt-1 w-full bg-gray-950 border border-gray-700 rounded-lg p-3" placeholder="🛡️">
    </div>
    <div class="md:col-span-2">
        <label class="text-xs uppercase text-gray-400">Logo URL</label>
        <input type="url" name="logo_url" value="{{ old('logo_url', $service?->logo_url) }}" class="mt-1 w-full bg-gray-950 border border-gray-700 rounded-lg p-3" placeholder="https://...">
    </div>
    <div class="md:col-span-2">
        <label class="text-xs uppercase text-gray-400">Short Description</label>
        <textarea name="description" rows="3" class="mt-1 w-full bg-gray-950 border border-gray-700 rounded-lg p-3" required>{{ old('description', $service?->description) }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label class="text-xs uppercase text-gray-400">Full Description</label>
        <textarea name="full_description" rows="6" class="mt-1 w-full bg-gray-950 border border-gray-700 rounded-lg p-3">{{ old('full_description', $service?->full_description) }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label class="text-xs uppercase text-gray-400">Python Script / Payload</label>
        <textarea name="script_code" rows="10" class="mt-1 w-full bg-gray-950 border border-gray-700 rounded-lg p-3 font-mono" placeholder="print('Service payload here')">{{ old('script_code', $service?->script_code) }}</textarea>
    </div>
    <label class="inline-flex items-center gap-2">
        <input type="hidden" name="is_visible" value="0">
        <input type="checkbox" name="is_visible" value="1" @checked(old('is_visible', $service?->is_visible ?? true)) class="rounded border-gray-600 bg-gray-900">
        <span class="text-sm text-gray-300">Visible on public website</span>
    </label>
    <label class="inline-flex items-center gap-2">
        <input type="hidden" name="is_automated" value="0">
        <input type="checkbox" name="is_automated" value="1" @checked(old('is_automated', $service?->is_automated ?? false)) class="rounded border-gray-600 bg-gray-900">
        <span class="text-sm text-gray-300">Automated service</span>
    </label>
</div>

@if ($errors->any())
    <div class="mt-4 rounded-lg border border-red-700 bg-red-900/20 p-4 text-sm text-red-300">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mt-6 flex gap-3">
    <button class="bg-karam-green text-black font-bold px-6 py-3 rounded-lg">{{ $isEdit ? 'Update Service' : 'Create Service' }}</button>
    <a href="{{ route('admin.services.index') }}" class="bg-gray-700 px-6 py-3 rounded-lg font-bold">Back</a>
</div>

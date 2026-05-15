@extends('layouts.app')

@section('title', 'Cybersecurity Courses')

@section('content')
<section class="py-16 bg-gray-900">
    <div class="max-w-7xl mx-auto px-6">
        <div class="mb-12">
            <h1 class="text-4xl font-black mb-4">Master <span class="text-karam-green">Ethical Hacking</span></h1>
            <p class="text-gray-400 text-lg">Structured courses with modules and lessons from the CyberWithKaram academy.</p>
        </div>

        @forelse($courses as $course)
            <div class="mb-10 bg-gray-800/40 border border-gray-800 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <span class="text-xs text-karam-green font-bold uppercase">{{ $course->level }}</span>
                        <h2 class="text-2xl font-bold">{{ $course->title }}</h2>
                        <p class="text-gray-400 text-sm mt-1">{{ $course->description }}</p>
                    </div>
                    @if($course->requires_purchase)
                        <span class="text-xs px-3 py-1 rounded-full bg-yellow-900/30 text-yellow-400 border border-yellow-800">Premium</span>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($course->modules as $module)
                        <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
                            <h3 class="font-bold text-karam-green mb-2">{{ $module->title }}</h3>
                            <ul class="space-y-2 text-sm text-gray-300">
                                @foreach($module->lessons as $lesson)
                                    <li class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500">#{{ $lesson->order_no }}</span>
                                        <span>{{ $lesson->title }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-gray-500">No courses published yet.</p>
        @endforelse
    </div>
</section>
@endsection



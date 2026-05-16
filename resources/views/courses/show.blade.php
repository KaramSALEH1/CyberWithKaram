@extends('layouts.app')

@section('title', $course->title)

@section('content')
    <section class="py-20 bg-[#0a0a0a] min-h-screen">
        <div class="max-w-5xl mx-auto px-6">
            <!-- Premium Header -->
            <div class="relative mb-20 p-12 bg-[#111] border border-white/5 rounded-[2.5rem] overflow-hidden">
                <div class="absolute -top-24 -right-24 h-64 w-64 bg-karam-green/10 blur-[100px] rounded-full"></div>

                <div class="relative">
                    <div class="flex items-center gap-4 mb-8">
                        <span
                            class="text-[10px] font-black bg-karam-green/10 text-karam-green border border-karam-green/20 px-4 py-1.5 rounded-full uppercase tracking-[0.2em] italic">Academy
                            Track</span>
                        <span
                            class="text-[10px] font-black bg-gray-800 text-gray-400 px-4 py-1.5 rounded-full uppercase tracking-[0.2em] italic">{{ $course->level }}</span>
                    </div>

                    <h1 class="text-6xl font-black text-white mb-6 italic tracking-tighter leading-none">{{ $course->title }}
                    </h1>
                    <p class="text-gray-500 text-xl font-medium leading-relaxed max-w-2xl">{{ $course->description }}</p>
                </div>
            </div>

            @if (!$hasCourseAccess)
                <!-- Lock Message -->
                <div class="mb-20 relative group">
                    <div
                        class="absolute -inset-1 bg-blue-600/20 rounded-3xl blur opacity-75 group-hover:opacity-100 transition duration-1000">
                    </div>
                    <div
                        class="relative bg-[#111] border border-blue-600/30 p-10 rounded-3xl flex flex-col md:flex-row items-center justify-between gap-8">
                        <div class="flex items-center gap-6">
                            <div
                                class="h-16 w-16 bg-blue-600/10 rounded-2xl flex items-center justify-center border border-blue-600/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-white italic uppercase tracking-tight mb-2">Unlock Full
                                    Academy Access</h3>
                                <p class="text-gray-500 font-medium">This professional track contains
                                    {{ $course->modules->pluck('lessons')->flatten()->count() }} technical lessons.</p>
                            </div>
                        </div>
                        <a href="{{ route('courses.checkout', $course->slug) }}"
                            class="w-full md:w-auto bg-blue-600 hover:bg-blue-500 text-white font-black px-10 py-4 rounded-2xl transition shadow-[0_0_30px_rgba(37,99,235,0.3)] uppercase italic tracking-widest">
                            ENROLL NOW
                        </a>
                    </div>
                </div>
            @endif

            <!-- Curriculum Grid -->
            <div class="space-y-16">
                <div class="flex items-center gap-4">
                    <div class="h-8 w-2 bg-karam-green"></div>
                    <h2 class="text-3xl font-black text-white italic uppercase tracking-tighter">Course Curriculum</h2>
                </div>

                @foreach ($course->modules as $module)
                    <div class="space-y-6">
                        <div class="flex items-center justify-between border-b border-white/5 pb-4">
                            <div class="flex flex-col">
                                <h3 class="text-lg font-black text-gray-400 uppercase tracking-widest italic">
                                    {{ $module->title }}</h3>
                                @if (!$hasCourseAccess)
                                    @if ($module->price > 0)
                                        <span
                                            class="text-xs font-bold text-karam-green mt-1">{{ number_format($module->price, 0) }}
                                            SYP (Module Only)</span>
                                    @elseif($course->price > 0)
                                        <span class="text-[10px] font-bold text-gray-500 mt-1 uppercase">Included in Full
                                            Track</span>
                                    @endif
                                @endif
                            </div>
                            <span class="text-[10px] font-bold text-gray-600">{{ $module->lessons->count() }} LESSONS</span>
                        </div>

                        <div class="grid grid-cols-1 gap-4">
                            @foreach ($module->lessons as $lesson)
                                @php
                                    $isAccessible =
                                        Auth::check() &&
                                        app(\App\Services\Academy\EntitlementService::class)->userHasLessonAccess(
                                            Auth::user(),
                                            $lesson,
                                        );
                                @endphp

                                @if ($isAccessible)
                                    <a href="{{ route('lessons.show', [$course->slug, $lesson->slug]) }}"
                                        class="group relative bg-[#111] border border-white/5 p-6 rounded-2xl flex items-center justify-between hover:border-karam-green/50 transition-all duration-300">
                                        <div class="flex items-center gap-6">
                                            <div
                                                class="h-12 w-12 bg-karam-green/5 rounded-xl flex items-center justify-center border border-karam-green/10 group-hover:border-karam-green/30 transition-colors">
                                                <span
                                                    class="font-mono text-sm text-karam-green">{{ str_pad($lesson->order_no, 2, '0', STR_PAD_LEFT) }}</span>
                                            </div>
                                            <div>
                                                <h4
                                                    class="font-black text-gray-200 italic group-hover:text-white transition-colors">
                                                    {{ $lesson->title }}</h4>
                                                <div class="flex items-center gap-2 mt-1">
                                                    <div
                                                        class="h-1.5 w-1.5 rounded-full bg-karam-green shadow-[0_0_8px_#00ff00]">
                                                    </div>
                                                    <span
                                                        class="text-[10px] font-bold text-karam-green uppercase tracking-widest">ACCESSIBLE</span>
                                                </div>
                                            </div>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-6 w-6 text-gray-700 group-hover:text-karam-green transition-colors"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </a>
                                @else
                                    <div
                                        class="bg-[#111]/50 border border-white/5 p-6 rounded-2xl flex items-center justify-between opacity-75 group transition-all duration-300">
                                        <div class="flex items-center gap-6">
                                            <div
                                                class="h-12 w-12 bg-gray-800/50 rounded-xl flex items-center justify-center border border-white/5">
                                                <span
                                                    class="font-mono text-sm text-gray-600">{{ str_pad($lesson->order_no, 2, '0', STR_PAD_LEFT) }}</span>
                                            </div>
                                            <div>
                                                <h4 class="font-black text-gray-500 italic">{{ $lesson->title }}</h4>
                                                <div class="flex flex-col gap-1 mt-1">
                                                    <span
                                                        class="text-[10px] font-bold text-red-900 uppercase tracking-widest">🔒
                                                        LOCKED</span>
                                                    @if ($lesson->price > 0)
                                                        <span
                                                            class="text-[10px] font-bold text-karam-green uppercase tracking-widest">{{ number_format($lesson->price, 0) }}
                                                            SYP (Lesson Only)</span>
                                                    @elseif($module->price > 0)
                                                        <span
                                                            class="text-[10px] font-bold text-gray-600 uppercase tracking-widest">Included
                                                            in Module</span>
                                                    @elseif($course->price > 0)
                                                        <span
                                                            class="text-[10px] font-bold text-gray-600 uppercase tracking-widest">Included
                                                            in Track</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @if ($lesson->price > 0)
                                            <a href="{{ route('lessons.checkout', $lesson->slug) }}"
                                                class="bg-karam-green/10 hover:bg-karam-green text-karam-green hover:text-black text-[10px] font-black px-4 py-2 rounded-lg border border-karam-green/20 transition-all uppercase italic">Buy
                                                Lesson</a>
                                        @elseif($module->price > 0)
                                            <a href="{{ route('modules.checkout', $module->id) }}"
                                                class="bg-blue-600/10 hover:bg-blue-600 text-blue-400 hover:text-white text-[10px] font-black px-4 py-2 rounded-lg border border-blue-600/20 transition-all uppercase italic">Buy
                                                Module</a>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection

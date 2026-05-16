@extends('layouts.app')

@section('title', $lesson->title)

@section('content')
<div class="flex flex-col lg:flex-row min-h-screen bg-[#0a0a0a]">
    <!-- Sleek Tree-View Sidebar -->
    <aside class="w-full lg:w-80 bg-[#111] border-r border-white/5 flex-shrink-0 flex flex-col">
        <div class="p-8 border-b border-white/5 bg-[#0a0a0a]/50">
            <a href="{{ route('courses.show', $course->slug) }}" class="inline-flex items-center gap-2 text-[10px] font-black text-karam-green hover:text-white transition-colors uppercase tracking-[0.2em] italic mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Course Index
            </a>
            <h2 class="text-xl font-black text-white italic leading-tight tracking-tighter">{{ $course->title }}</h2>
        </div>

        <nav class="flex-1 overflow-y-auto p-6 space-y-10 custom-scrollbar">
            @foreach($course->modules as $module)
                <div class="space-y-4">
                    <h3 class="text-[10px] font-black text-gray-600 uppercase tracking-[0.3em] px-2 italic">{{ $module->title }}</h3>
                    <div class="space-y-1">
                        @foreach($module->lessons as $m_lesson)
                            @php
                                $isAccessible = Auth::check() && app(\App\Services\Academy\EntitlementService::class)->userHasLessonAccess(Auth::user(), $m_lesson);
                                $isActive = $lesson->id === $m_lesson->id;
                            @endphp

                            @if($isAccessible)
                                <a href="{{ route('lessons.show', [$course->slug, $m_lesson->slug]) }}" 
                                   class="group flex items-center gap-4 p-3 rounded-xl transition-all duration-300 {{ $isActive ? 'bg-karam-green/10 border border-karam-green/20' : 'hover:bg-white/5 border border-transparent' }}">
                                    <span class="font-mono text-[10px] {{ $isActive ? 'text-karam-green' : 'text-gray-600' }}">{{ str_pad($m_lesson->order_no, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="text-sm font-bold truncate {{ $isActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-200' }} italic">{{ $m_lesson->title }}</span>
                                    @if($isActive)
                                        <div class="ml-auto h-1.5 w-1.5 rounded-full bg-karam-green shadow-[0_0_8px_#00ff00]"></div>
                                    @endif
                                </a>
                            @else
                                <div class="flex items-center gap-4 p-3 text-gray-700 cursor-not-allowed opacity-40">
                                    <span class="font-mono text-[10px]">{{ str_pad($m_lesson->order_no, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="text-sm font-bold truncate italic">{{ $m_lesson->title }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>
    </aside>

    <!-- Content Area -->
    <main class="flex-1 overflow-y-auto bg-[#0a0a0a]">
        <div class="max-w-5xl mx-auto px-6 py-12 lg:px-16">
            <!-- Dual Video Player -->
            <div class="relative mb-12 group">
                <div class="absolute -inset-1 bg-karam-green/10 rounded-3xl blur opacity-0 group-hover:opacity-100 transition duration-1000"></div>
                <div class="relative aspect-video w-full bg-black rounded-3xl overflow-hidden border border-white/5 shadow-2xl">
                    @if($lesson->video_type === 'youtube' && $lesson->video_url)
                        <iframe 
                            class="w-full h-full" 
                            src="https://www.youtube.com/embed/{{ $lesson->video_url }}?rel=0&modestbranding=1&autoplay=0" 
                            title="Lesson Video" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                    @elseif($lesson->video_type === 'local' && $lesson->video_path)
                        <video controls class="w-full h-full object-cover" poster="{{ asset('images/video-poster.jpg') }}">
                            <source src="{{ asset('storage/' . $lesson->video_path) }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-gray-600 bg-[#111]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <p class="font-black italic uppercase tracking-widest text-sm">Video content unavailable</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Lesson Info -->
            <div class="mb-12 border-b border-white/5 pb-10">
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-[10px] font-black text-karam-green uppercase tracking-[0.3em] italic">{{ $lesson->module->title }}</span>
                    @if($lesson->is_free)
                        <span class="text-[10px] font-black bg-green-900/20 text-green-400 border border-green-500/20 px-3 py-1 rounded-full uppercase italic">Free Preview</span>
                    @endif
                </div>
                <h1 class="text-5xl font-black text-white italic tracking-tighter leading-tight">{{ $lesson->title }}</h1>
            </div>

            <!-- Workspace / Content -->
            <div class="relative">
                <div class="absolute -left-8 top-0 bottom-0 w-1 bg-gradient-to-b from-karam-green/20 to-transparent rounded-full"></div>
                <div class="prose prose-invert prose-karam max-w-none prose-headings:italic prose-headings:tracking-tighter prose-strong:text-karam-green">
                    {!! $lesson->content !!}
                </div>
            </div>

            <!-- Premium Navigation -->
            <div class="mt-20 pt-10 border-t border-white/5 flex flex-col sm:flex-row justify-between items-center gap-8">
                @if($prevLesson)
                    <a href="{{ route('lessons.show', [$course->slug, $prevLesson->slug]) }}" class="w-full sm:w-auto flex flex-col items-start group">
                        <span class="text-[10px] font-black text-gray-600 uppercase tracking-[0.3em] mb-3 italic">Previous Lesson</span>
                        <div class="flex items-center gap-4 bg-[#111] border border-white/5 px-6 py-4 rounded-2xl group-hover:border-karam-green/30 transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 group-hover:text-karam-green transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            <span class="text-gray-300 group-hover:text-white transition-colors font-black italic">{{ $prevLesson->title }}</span>
                        </div>
                    </a>
                @else
                    <div class="hidden sm:block"></div>
                @endif

                @if($nextLesson)
                    <a href="{{ route('lessons.show', [$course->slug, $nextLesson->slug]) }}" class="w-full sm:w-auto flex flex-col items-end group">
                        <span class="text-[10px] font-black text-gray-600 uppercase tracking-[0.3em] mb-3 italic">Next Lesson</span>
                        <div class="flex items-center gap-4 bg-[#111] border border-white/5 px-6 py-4 rounded-2xl group-hover:border-karam-green/30 transition-all duration-300">
                            <span class="text-gray-300 group-hover:text-white transition-colors font-black italic">{{ $nextLesson->title }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 group-hover:text-karam-green transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </a>
                @endif
            </div>
        </div>
    </main>
</div>

<style>
    .prose-karam {
        --tw-prose-body: #9ca3af;
        --tw-prose-headings: #ffffff;
        --tw-prose-links: #00ff00;
        --tw-prose-bold: #ffffff;
        --tw-prose-counters: #00ff00;
        --tw-prose-bullets: #00ff00;
        --tw-prose-quotes: #00ff00;
        --tw-prose-code: #00ff00;
        --tw-prose-hr: rgba(255,255,255,0.05);
        --tw-prose-th-borders: rgba(255,255,255,0.1);
    }
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.05);
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(0,255,0,0.2);
    }
</style>
@endsection

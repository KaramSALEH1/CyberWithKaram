@extends('layouts.app')

@section('title', 'Cybersecurity Academy Tracks')

@section('content')
    <section class="py-20 bg-[#0a0a0a] min-h-screen">
        <div class="max-w-7xl mx-auto px-6">
            <div class="mb-16 text-center">
                <h1 class="text-6xl font-black mb-4 tracking-tighter italic">
                    <span class="text-white">ACADEMY</span>
                    <span class="text-karam-green">TRACKS</span>
                </h1>
                <p class="text-gray-500 text-xl font-medium max-w-2xl mx-auto">Master professional cybersecurity skills
                    through structured, hands-on learning paths.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($courses as $course)
                    <div
                        class="group relative bg-[#111] border border-white/5 rounded-3xl p-8 hover:border-karam-green/50 transition-all duration-500 hover:-translate-y-2 overflow-hidden">
                        <!-- Glow Effect -->
                        <div
                            class="absolute -inset-1 bg-karam-green/20 rounded-3xl blur opacity-0 group-hover:opacity-100 transition duration-500">
                        </div>

                        <div class="relative flex flex-col h-full">
                            <div class="flex justify-between items-start mb-8">
                                <span
                                    class="text-[10px] font-black bg-karam-green/10 text-karam-green border border-karam-green/20 px-3 py-1 rounded-full uppercase tracking-widest">
                                    Academy Track
                                </span>
                                <span class="text-[10px] font-black text-gray-600 uppercase tracking-widest">
                                    {{ $course->level }}
                                </span>
                            </div>

                            <h2
                                class="text-3xl font-black text-white mb-2 italic group-hover:text-karam-green transition-colors">
                                {{ $course->title }}</h2>
                            
                            <div class="mb-4">
                                @if($course->price > 0)
                                    <span class="text-xl font-bold text-karam-green">{{ number_format($course->price, 0) }} SYP</span>
                                @else
                                    <span class="text-xl font-bold text-karam-green">FREE</span>
                                @endif
                            </div>

                            <p class="text-gray-500 text-sm leading-relaxed mb-8 flex-grow">
                                {{ Str::limit($course->description, 120) }}</p>

                            <div class="flex items-center justify-between mt-auto">
                                <div class="flex -space-x-2">
                                    <div
                                        class="h-8 w-8 rounded-full border-2 border-[#111] bg-gray-800 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-karam-green"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                    <div
                                        class="h-8 w-8 rounded-full border-2 border-[#111] bg-gray-800 flex items-center justify-center text-[10px] font-bold text-white">
                                        {{ $course->modules->count() }}
                                    </div>
                                </div>

                                <a href="{{ route('courses.show', $course->slug) }}"
                                    class="inline-flex items-center gap-2 bg-karam-green text-black font-black px-6 py-3 rounded-xl hover:scale-105 transition shadow-[0_0_20px_rgba(0,255,0,0.2)]">
                                    EXPLORE
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-20 text-center border-2 border-dashed border-white/5 rounded-3xl">
                        <p class="text-gray-600 font-bold uppercase tracking-widest italic">No academy tracks published yet.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection

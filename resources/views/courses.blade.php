@extends('layouts.app')

@section('title', 'Cybersecurity Courses')

@section('content')
    <section class="py-16 bg-gray-900">
        <div class="max-w-7xl mx-auto px-6">
            <div class="mb-12">
                <h1 class="text-4xl font-black mb-4">Master <span class="text-karam-green">Ethical Hacking</span></h1>
                <p class="text-gray-400 text-lg">Learn hands-on cybersecurity directly from my YouTube channel lessons.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                <div class="lg:col-span-2">
                    <div class="aspect-video rounded-2xl overflow-hidden border border-gray-800 shadow-2xl bg-black">
                        <iframe class="w-full h-full" src="https://www.youtube.com/embed/dQw4w9WgXcQ"
                            title="Cybersecurity Lesson" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen>
                        </iframe>
                    </div>

                    <div class="mt-8 p-8 bg-gray-800/30 border border-gray-800 rounded-2xl">
                        <div class="flex items-center gap-4 mb-6">
                            <span
                                class="bg-karam-green/20 text-karam-green px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">Current
                                Lesson</span>
                            <h2 class="text-2xl font-bold">Introduction to Web Penetration Testing</h2>
                        </div>
                        <article class="prose prose-invert max-w-none text-gray-400 leading-relaxed">
                            <p class="mb-4">
                                In this comprehensive lesson, we dive deep into the world of web security. We will explore
                                how to identify common vulnerabilities like SQL Injection and XSS using professional tools.
                            </p>
                            <h3 class="text-white font-bold mb-2">What you will learn:</h3>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>Setting up a secure testing environment.</li>
                                <li>Understanding the HTTP protocol from a security perspective.</li>
                                <li>Automating vulnerability scanning with Python scripts.</li>
                            </ul>
                        </article>

                        <div
                            class="mt-10 p-6 bg-karam-green/5 border border-karam-green/20 rounded-xl flex items-center justify-between">
                            <div>
                                <p class="font-bold text-white">Enjoying the content?</p>
                                <p class="text-sm text-gray-400">Subscribe to my YouTube channel for more updates.</p>
                            </div>
                            <a href="#"
                                class="bg-red-600 hover:bg-red-700 px-6 py-2 rounded-lg font-bold transition">Subscribe</a>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <span class="text-karam-green">●</span> Course Syllabus
                    </h3>

                    <div class="p-4 bg-gray-800 border-l-4 border-karam-green rounded-r-lg cursor-pointer transition">
                        <p class="text-xs text-karam-green font-bold mb-1">LESSON 01</p>
                        <p class="font-bold text-white">Intro to VAPT Environment</p>
                        <p class="text-xs text-gray-500 mt-2">12:45 Mins</p>
                    </div>

                    <div
                        class="p-4 bg-gray-900 border border-gray-800 rounded-lg hover:border-gray-700 cursor-pointer group transition">
                        <p class="text-xs text-gray-500 font-bold mb-1">LESSON 02</p>
                        <p class="font-bold text-gray-300 group-hover:text-white transition">Information Gathering
                            Techniques</p>
                        <p class="text-xs text-gray-500 mt-2">18:20 Mins</p>
                    </div>

                    <div
                        class="p-4 bg-gray-900 border border-gray-800 rounded-lg hover:border-gray-700 cursor-pointer group transition">
                        <p class="text-xs text-gray-500 font-bold mb-1">LESSON 03</p>
                        <p class="font-bold text-gray-300 group-hover:text-white transition">Exploitation Frameworks</p>
                        <p class="text-xs text-gray-500 mt-2">25:00 Mins</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

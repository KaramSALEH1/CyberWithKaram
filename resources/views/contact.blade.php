@extends('layouts.app')
@section('title', 'Contact Me')

@section('content')
    <section class="py-24 px-6 max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-16">
            <div>
                <h1 class="text-5xl font-black mb-8 text-karam-green">Let's Talk Security.</h1>
                <p class="text-gray-400 text-lg mb-10">Have a project or a security concern? Reach out through any of these
                    platforms:</p>

                <div class="space-y-6">
                    <div class="flex items-center gap-4">
                        <span class="text-2xl">📧</span>
                        <a href="mailto:karam.saleh.cs@gmail.com"
                            class="text-xl font-medium hover:text-karam-green transition">karam.saleh.cs@gmail.com</a>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-2xl">📸</span>
                        <a href="https://instagram.com/k_cs0"
                            class="text-xl font-medium hover:text-karam-green transition">Instagram: k_cs0</a>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-2xl">👤</span>
                        <span class="text-xl font-medium">Facebook: Karam SALEH</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-2xl">🔗</span>
                        <span class="text-xl font-medium">LinkedIn: Karam SALEH</span>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 p-8 rounded-2xl border border-gray-700 shadow-xl">
                <form class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2 uppercase">Full Name</label>
                        <input type="text"
                            class="w-full bg-gray-900 border border-gray-700 rounded-lg p-4 focus:border-karam-green outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2 uppercase">Email</label>
                        <input type="email"
                            class="w-full bg-gray-900 border border-gray-700 rounded-lg p-4 focus:border-karam-green outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2 uppercase">Your Message</label>
                        <textarea rows="4"
                            class="w-full bg-gray-900 border border-gray-700 rounded-lg p-4 focus:border-karam-green outline-none transition"></textarea>
                    </div>
                    <button type="submit"
                        class="w-full bg-karam-green py-4 rounded-lg font-black text-lg hover:opacity-90 transition">SEND
                        INQUIRY</button>
                </form>
            </div>
        </div>
    </section>
@endsection

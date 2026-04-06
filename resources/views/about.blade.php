@extends('layouts.app')
@section('title', 'About Me')

@section('content')
    <section class="py-24 px-6 max-w-5xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div>
                <h1 class="text-5xl font-black mb-6">About <span class="text-karam-green">KARAM</span></h1>
                <p class="text-gray-400 text-xl leading-relaxed mb-6">
                    I am a dedicated **Cybersecurity Professional** and Business Owner, specializing in Penetration Testing
                    and Vulnerability Assessments.
                </p>
                <p class="text-gray-400 leading-relaxed">
                    My mission is to help companies secure their digital borders by finding vulnerabilities before hackers
                    do. With a deep passion for ethical hacking and coding, I bridge the gap between development and
                    security.
                </p>
            </div>
            <div class="bg-gray-800 rounded-2xl p-2 border border-gray-700">
                <div class="aspect-square bg-gray-900 rounded-xl flex items-center justify-center">
                    <img src="{{ asset('images/logo.png') }}" class="h-40 opacity-50" alt="Profile Shield">
                </div>
            </div>
        </div>
    </section>
@endsection

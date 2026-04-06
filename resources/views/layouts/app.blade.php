<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KARAM Security | @yield('title', 'Secure Today')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .text-karam-green {
            color: #008751;
        }

        .bg-karam-green {
            background-color: #008751;
        }

        .border-karam-green {
            border-color: #008751;
        }
    </style>
</head>

<body class="bg-gray-900 text-white font-sans selection:bg-karam-green flex flex-col min-h-screen">

    <nav class="p-6 border-b border-gray-800 sticky top-0 bg-gray-900/80 backdrop-blur-md z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="KARAM Logo" class="h-10">
                <span class="text-2xl font-bold tracking-tighter text-karam-green">KARAM</span>
            </a>
            <div class="hidden md:flex space-x-8 items-center text-sm font-medium">
                <a href="{{ route('home') }}" class="hover:text-karam-green transition">Home</a>
                <a href="{{ route('about') }}" class="hover:text-karam-green transition">About</a>
                <a href="{{ route('services') }}" class="hover:text-karam-green transition">Services</a>
                <a href="{{ route('courses') }}" class="hover:text-karam-green transition">Courses</a>
                <a href="{{ route('contact') }}"
                    class="bg-karam-green px-5 py-2 rounded-md font-bold hover:opacity-90 transition shadow-lg shadow-karam-green/20">Contact
                    Us</a>
            </div>
        </div>
    </nav>

    <main class="flex-grow">
        @yield('content')
    </main>

    <footer class="py-12 border-t border-gray-800 bg-gray-900">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <p class="text-gray-500 text-sm mb-4">© 2026 KARAM Security. All rights reserved.</p>
            <div class="flex justify-center space-x-6 text-gray-400">
                <a href="https://instagram.com/k_cs0" class="hover:text-karam-green">Instagram</a>
                <a href="#" class="hover:text-karam-green">LinkedIn</a>
                <a href="#" class="hover:text-karam-green">Facebook</a>
            </div>
        </div>
    </footer>
</body>

</html>

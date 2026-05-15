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
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-cyan-400' : 'hover:text-cyan-400' }} transition">Home</a>
                <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'text-cyan-400' : 'hover:text-cyan-400' }} transition">About</a>
                <a href="{{ route('services') }}" class="{{ request()->routeIs('services') ? 'text-cyan-400' : 'hover:text-cyan-400' }} transition">Services</a>
                <a href="{{ route('courses') }}" class="{{ request()->routeIs('courses') ? 'text-cyan-400' : 'hover:text-cyan-400' }} transition">Courses</a>
                
                @auth
                    <a href="{{ route('my-tools.index') }}" class="{{ request()->routeIs('my-tools.*') ? 'text-cyan-400' : 'hover:text-cyan-400' }} transition">My Tools</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="hover:text-red-400 transition">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hover:text-cyan-400 transition">Login</a>
                @endauth

                <a href="{{ route('contact') }}"
                    class="bg-cyan-500 text-black px-5 py-2 rounded-md font-bold hover:bg-cyan-400 transition shadow-lg shadow-cyan-500/20">Contact
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

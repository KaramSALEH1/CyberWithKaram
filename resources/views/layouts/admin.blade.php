<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') | CyberWithKaram</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .text-karam-green { color: #008751; }
        .bg-karam-green { background-color: #008751; }
        .border-karam-green { border-color: #008751; }
    </style>
</head>
<body class="bg-gray-950 text-white min-h-screen">
    <div class="flex min-h-screen">
        <aside class="w-64 bg-gray-900 border-r border-gray-800 p-6 hidden lg:block">
            <a href="{{ route('dashboard') }}" class="text-xl font-black text-karam-green block mb-8">CYBER COMMAND</a>
            <nav class="space-y-2 text-sm">
                <a href="{{ route('dashboard') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-karam-green/20 text-karam-green border border-karam-green/40' : 'bg-gray-800 border border-gray-700 hover:border-karam-green' }}">Dashboard</a>
                <a href="{{ route('admin.services.index') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('admin.services.*') ? 'bg-karam-green/20 text-karam-green border border-karam-green/40' : 'bg-gray-800 border border-gray-700 hover:border-karam-green' }}">Services</a>
                <a href="{{ route('admin.payments.index') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('admin.payments.*') ? 'bg-karam-green/20 text-karam-green border border-karam-green/40' : 'bg-gray-800 border border-gray-700 hover:border-karam-green' }}">Payments</a>
                <a href="{{ route('admin.academy.index') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('admin.academy.*', 'admin.course.*') ? 'bg-karam-green/20 text-karam-green border border-karam-green/40' : 'bg-gray-800 border border-gray-700 hover:border-karam-green' }}">Academy</a>
                <a href="{{ route('admin.command-center.index') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('admin.command-center.*') ? 'bg-karam-green/20 text-karam-green border border-karam-green/40' : 'bg-gray-800 border border-gray-700 hover:border-karam-green' }}">Agent Center</a>
            </nav>
        </aside>
        <main class="flex-1 p-6 lg:p-10">
            @if (session('success'))
                <div class="mb-4 rounded-lg border border-green-700 bg-green-900/20 p-4 text-green-300">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-lg border border-red-700 bg-red-900/20 p-4 text-red-300">{{ session('error') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>

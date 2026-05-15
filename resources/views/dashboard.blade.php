@extends('layouts.admin')

@section('title', 'Command Center')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-3xl font-black">CyberWithKaram <span class="text-karam-green">Command Center</span></h1>
        <p class="text-gray-400 text-sm mt-1">Centralized control for services, payments, academy, and agents.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase">Services</p>
            <p class="text-3xl font-black text-karam-green">{{ $services->count() }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase">Pending Payments</p>
            <p class="text-3xl font-black text-yellow-400">{{ $pendingPayments }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase">Courses</p>
            <p class="text-3xl font-black text-blue-400">{{ $coursesCount }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase">Online Agents</p>
            <p class="text-3xl font-black text-green-400">{{ $onlineAgents }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
            <h2 class="text-lg font-bold text-karam-green mb-4">Quick Actions</h2>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <a href="{{ route('admin.services.index') }}" class="bg-gray-950 border border-gray-700 rounded-lg px-4 py-3 hover:border-karam-green">Manage Services</a>
                <a href="{{ route('admin.payments.index') }}" class="bg-gray-950 border border-gray-700 rounded-lg px-4 py-3 hover:border-karam-green">Verify Payments</a>
                <a href="{{ route('admin.academy.index') }}" class="bg-gray-950 border border-gray-700 rounded-lg px-4 py-3 hover:border-karam-green">Manage Academy</a>
                <a href="{{ route('admin.command-center.index') }}" class="bg-gray-950 border border-gray-700 rounded-lg px-4 py-3 hover:border-karam-green">Agent Center</a>
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
            <h2 class="text-lg font-bold text-karam-green mb-4">Services Snapshot</h2>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach ($services as $service)
                    <div class="flex items-center justify-between bg-gray-950 border border-gray-800 rounded-lg p-3 text-sm">
                        <span>{{ $service->title }}</span>
                        <a href="{{ route('admin.services.show', $service) }}" class="text-karam-green font-bold">Manage</a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection


@extends('layouts.app')

@section('title', 'Create Service')

@section('content')
<div class="py-10 bg-gray-900 min-h-screen text-white">
    <div class="max-w-5xl mx-auto px-6">
        <h1 class="text-3xl font-black mb-6">Create <span class="text-karam-green">Service</span></h1>
        <form action="{{ route('admin.services.store') }}" method="POST" class="bg-gray-800 border border-gray-700 rounded-2xl p-6">
            @csrf
            @include('admin.services._form')
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="py-10 bg-gray-900 min-h-screen text-white">
    <div class="max-w-7xl mx-auto px-6 space-y-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h1 class="text-3xl font-black">Admin <span class="text-karam-green">Command Center</span></h1>
            <span class="text-sm text-gray-400">Agents: {{ $agents->count() }} | Commands: {{ $commands->total() }}</span>
        </div>

        <div class="bg-gray-800 border border-gray-700 rounded-2xl p-6">
            <h2 class="text-lg font-bold text-karam-green mb-4">Dispatch Command</h2>
            <form method="POST" action="{{ route('admin.command-center.commands.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @csrf
                <select name="agent_id" class="bg-gray-900 border border-gray-700 rounded-lg p-3">
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}">{{ $agent->device_name }} ({{ $agent->status }})</option>
                    @endforeach
                </select>
                <select name="command_key" class="bg-gray-900 border border-gray-700 rounded-lg p-3">
                    <option value="collect_inventory">Collect Inventory</option>
                    <option value="run_health_check">Run Health Check</option>
                    <option value="sync_course_unlocks">Sync Course Unlocks</option>
                </select>
                <input type="number" name="ttl_seconds" value="300" min="30" max="3600" class="bg-gray-900 border border-gray-700 rounded-lg p-3" />
                <button type="submit" class="bg-karam-green text-black rounded-lg p-3 font-bold">Queue Command</button>
            </form>
        </div>

        <div class="bg-gray-800 border border-gray-700 rounded-2xl p-6">
            <h2 class="text-lg font-bold text-blue-400 mb-4">Command Activity</h2>
            <div class="space-y-3">
                @forelse($commands as $command)
                    <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <div>
                                <p class="font-bold">{{ $command->command_key }} <span class="text-xs text-gray-500">{{ $command->command_uuid }}</span></p>
                                <p class="text-xs text-gray-400">Agent: {{ $command->agent?->device_name }} | Status: {{ strtoupper($command->status) }}</p>
                            </div>
                            @if(!in_array($command->status, ['succeeded', 'failed', 'cancelled', 'expired']))
                                <form method="POST" action="{{ route('admin.command-center.commands.cancel', $command) }}" class="flex gap-2">
                                    @csrf
                                    <input type="hidden" name="reason" value="Cancelled from dashboard" />
                                    <button type="submit" class="px-3 py-2 rounded bg-red-900 text-red-300 text-xs font-bold">Cancel</button>
                                </form>
                            @endif
                        </div>
                        @if($command->result)
                            <div class="mt-3 text-xs text-gray-400 border-t border-gray-800 pt-3">
                                Exit: {{ $command->result->exit_code ?? 'N/A' }} | Duration: {{ $command->result->duration_ms ?? 'N/A' }} ms
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No command activity yet.</p>
                @endforelse
            </div>
            <div class="mt-4">{{ $commands->links() }}</div>
        </div>
    </div>
</div>
@endsection

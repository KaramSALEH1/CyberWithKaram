<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommandCenter\CancelAgentCommandRequest;
use App\Http\Requests\CommandCenter\StoreAgentCommandRequest;
use App\Models\Agent;
use App\Models\AgentCommand;
use App\Services\CommandCenter\CommandDispatchService;

class CommandCenterController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', AgentCommand::class);

        $agents = Agent::query()->latest()->get();
        $commands = AgentCommand::with(['agent', 'result', 'requester'])
            ->latest()
            ->paginate(20);

        return view('admin.command-center.index', compact('agents', 'commands'));
    }

    public function store(StoreAgentCommandRequest $request, CommandDispatchService $dispatchService)
    {
        $this->authorize('create', AgentCommand::class);

        $agent = Agent::findOrFail($request->integer('agent_id'));
        $dispatchService->dispatch(
            $agent,
            $request->user(),
            $request->string('command_key')->toString(),
            $request->input('payload', []),
            $request->integer('ttl_seconds') ?: 300
        );

        return back()->with('success', 'Command queued successfully.');
    }

    public function cancel(CancelAgentCommandRequest $request, AgentCommand $command, CommandDispatchService $dispatchService)
    {
        $this->authorize('delete', $command);
        $dispatchService->cancel($command, $request->string('reason')->toString());

        return back()->with('success', 'Command cancelled.');
    }
}

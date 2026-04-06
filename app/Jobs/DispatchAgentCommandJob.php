<?php

namespace App\Jobs;

use App\Models\AgentCommand;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchAgentCommandJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $commandId
    ) {
    }

    public function handle(): void
    {
        $command = AgentCommand::find($this->commandId);
        if (!$command || $command->status !== 'queued') {
            return;
        }
    }
}

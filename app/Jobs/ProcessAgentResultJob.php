<?php

namespace App\Jobs;

use App\Models\AgentCommandResult;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessAgentResultJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $resultId
    ) {
    }

    public function handle(): void
    {
        $result = AgentCommandResult::with('command')->find($this->resultId);
        if (!$result || !$result->command) {
            return;
        }

        $result->command->update([
            'status' => $result->result_status === 'succeeded' ? 'succeeded' : 'failed',
            'finished_at' => now(),
        ]);
    }
}

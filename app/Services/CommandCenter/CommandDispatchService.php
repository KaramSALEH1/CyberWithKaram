<?php

namespace App\Services\CommandCenter;

use App\Jobs\DispatchAgentCommandJob;
use App\Models\Agent;
use App\Models\AgentCommand;
use App\Models\User;
use Illuminate\Support\Str;

class CommandDispatchService
{
    public function __construct(
        private readonly CommandSigningService $signingService
    ) {
    }

    public function dispatch(Agent $agent, User $requester, string $commandKey, array $payload = [], ?int $ttlSeconds = 300): AgentCommand
    {
        $nonce = Str::uuid()->toString();
        $expiresAt = $ttlSeconds ? now()->addSeconds($ttlSeconds) : null;
        $signature = $this->signingService->sign($commandKey, $payload, $nonce, $expiresAt?->toIso8601String());

        $command = AgentCommand::create([
            'command_uuid' => (string) Str::uuid(),
            'agent_id' => $agent->id,
            'requested_by' => $requester->id,
            'approved_by' => $requester->id,
            'command_key' => $commandKey,
            'payload' => $payload,
            'signature_hash' => $signature,
            'nonce' => $nonce,
            'expires_at' => $expiresAt,
            'status' => 'queued',
            'queued_at' => now(),
        ]);

        DispatchAgentCommandJob::dispatch($command->id);

        return $command;
    }

    public function cancel(AgentCommand $command, string $reason): AgentCommand
    {
        if (in_array($command->status, ['succeeded', 'failed', 'cancelled', 'expired'], true)) {
            return $command;
        }

        $command->update([
            'status' => 'cancelled',
            'cancel_reason' => $reason,
            'cancelled_at' => now(),
            'finished_at' => now(),
        ]);

        return $command->refresh();
    }
}

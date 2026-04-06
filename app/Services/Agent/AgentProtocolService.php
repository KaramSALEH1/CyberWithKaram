<?php

namespace App\Services\Agent;

use App\Jobs\ProcessAgentResultJob;
use App\Models\Agent;
use App\Models\AgentCommand;
use App\Models\AgentCommandResult;
use App\Models\AgentHeartbeat;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AgentProtocolService
{
    public function register(array $payload): array
    {
        $plainToken = Str::random(64);

        $agent = Agent::create([
            'user_id' => $payload['user_id'],
            'agent_key' => $payload['agent_key'],
            'api_token_hash' => hash('sha256', $plainToken),
            'device_name' => $payload['device_name'],
            'ip_address' => Arr::get($payload, 'ip_address'),
            'os_type' => Arr::get($payload, 'os_type'),
            'agent_version' => Arr::get($payload, 'agent_version'),
            'host_fingerprint' => Arr::get($payload, 'host_fingerprint'),
            'status' => 'online',
            'last_seen' => now(),
            'token_last_rotated_at' => now(),
            'metadata' => Arr::get($payload, 'metadata', []),
            'registered_at' => now(),
        ]);

        return [$agent, $plainToken];
    }

    public function heartbeat(Agent $agent, array $payload): AgentHeartbeat
    {
        $agent->update([
            'status' => Arr::get($payload, 'status', 'online'),
            'ip_address' => Arr::get($payload, 'ip_address', $agent->ip_address),
            'os_type' => Arr::get($payload, 'os_type', $agent->os_type),
            'agent_version' => Arr::get($payload, 'agent_version', $agent->agent_version),
            'host_fingerprint' => Arr::get($payload, 'host_fingerprint', $agent->host_fingerprint),
            'last_seen' => now(),
            'metadata' => array_merge($agent->metadata ?? [], Arr::get($payload, 'metadata', [])),
        ]);

        return AgentHeartbeat::create([
            'agent_id' => $agent->id,
            'status' => Arr::get($payload, 'status', 'online'),
            'ip_address' => Arr::get($payload, 'ip_address', $agent->ip_address),
            'os_type' => Arr::get($payload, 'os_type', $agent->os_type),
            'agent_version' => Arr::get($payload, 'agent_version', $agent->agent_version),
            'host_fingerprint' => Arr::get($payload, 'host_fingerprint', $agent->host_fingerprint),
            'metadata' => Arr::get($payload, 'metadata', []),
            'seen_at' => now(),
        ]);
    }

    public function nextCommand(Agent $agent): ?AgentCommand
    {
        $command = AgentCommand::where('agent_id', $agent->id)
            ->where('status', 'queued')
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderBy('id')
            ->first();

        if (!$command) {
            return null;
        }

        $command->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return $command->refresh();
    }

    public function storeResult(Agent $agent, array $payload): AgentCommandResult
    {
        $command = AgentCommand::where('command_uuid', $payload['command_uuid'])
            ->where('agent_id', $agent->id)
            ->firstOrFail();

        if ($agent->last_nonce === $payload['nonce']) {
            abort(409, 'Replay detected.');
        }

        $result = AgentCommandResult::updateOrCreate(
            ['agent_command_id' => $command->id],
            [
                'result_status' => $payload['result_status'],
                'exit_code' => Arr::get($payload, 'exit_code'),
                'duration_ms' => Arr::get($payload, 'duration_ms'),
                'stdout' => Arr::get($payload, 'stdout'),
                'stderr' => Arr::get($payload, 'stderr'),
                'result_hash' => hash('sha256', ($payload['stdout'] ?? '').($payload['stderr'] ?? '')),
                'artifacts' => Arr::get($payload, 'artifacts', []),
                'received_at' => now(),
            ]
        );

        $agent->update(['last_nonce' => $payload['nonce'], 'last_seen' => now()]);
        ProcessAgentResultJob::dispatch($result->id);

        return $result;
    }
}

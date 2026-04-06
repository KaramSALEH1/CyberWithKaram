<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AgentHeartbeatRequest;
use App\Http\Requests\Api\AgentPollRequest;
use App\Http\Requests\Api\AgentRegisterRequest;
use App\Http\Requests\Api\AgentResultRequest;
use App\Models\Agent;
use App\Services\Agent\AgentProtocolService;

class AgentController extends Controller
{
    public function register(AgentRegisterRequest $request, AgentProtocolService $service)
    {
        [$agent, $plainToken] = $service->register($request->validated());

        return response()->json([
            'agent_id' => $agent->id,
            'agent_key' => $agent->agent_key,
            'api_token' => $plainToken,
        ], 201);
    }

    public function heartbeat(AgentHeartbeatRequest $request, AgentProtocolService $service)
    {
        /** @var Agent $agent */
        $agent = $request->attributes->get('agent');
        $service->heartbeat($agent, $request->validated());

        return response()->json(['ok' => true]);
    }

    public function poll(AgentPollRequest $request, AgentProtocolService $service)
    {
        /** @var Agent $agent */
        $agent = $request->attributes->get('agent');
        $command = $service->nextCommand($agent);

        if (!$command) {
            return response()->json(['command' => null]);
        }

        return response()->json([
            'command' => [
                'uuid' => $command->command_uuid,
                'key' => $command->command_key,
                'payload' => $command->payload,
                'signature_hash' => $command->signature_hash,
                'nonce' => $command->nonce,
                'expires_at' => $command->expires_at?->toIso8601String(),
            ],
        ]);
    }

    public function result(AgentResultRequest $request, AgentProtocolService $service)
    {
        /** @var Agent $agent */
        $agent = $request->attributes->get('agent');
        $result = $service->storeResult($agent, $request->validated());

        return response()->json([
            'result_id' => $result->id,
            'status' => $result->result_status,
        ]);
    }
}

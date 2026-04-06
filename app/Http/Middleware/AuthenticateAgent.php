<?php

namespace App\Http\Middleware;

use App\Models\Agent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAgent
{
    public function handle(Request $request, Closure $next): Response
    {
        $agentKey = $request->header('X-Agent-Key');
        $token = $request->bearerToken();

        if (!$agentKey || !$token) {
            abort(401, 'Missing agent credentials.');
        }

        $agent = Agent::where('agent_key', $agentKey)->first();

        if (!$agent || !$agent->api_token_hash || !hash_equals($agent->api_token_hash, hash('sha256', $token))) {
            abort(401, 'Invalid agent credentials.');
        }

        $request->attributes->set('agent', $agent);

        return $next($request);
    }
}

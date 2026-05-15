<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AgentHeartbeatApiRequest;
use App\Http\Requests\Api\FetchScriptRequest;
use App\Models\AgentStatus;
use App\Models\Service;
use App\Services\Payment\PaymentVerificationService;
use App\Services\Telegram\TelegramService; // <--- Add this
use Illuminate\Http\Request;

class AgentApiController extends Controller
{
    // Inject TelegramService
    public function __construct(private TelegramService $telegramService)
    {
    }

    public function heartbeat(AgentHeartbeatApiRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        $service = Service::findOrFail($data['service_id']); // Fetch the service

        $agentStatus = AgentStatus::firstOrNew( // <--- Use firstOrNew
            [
                'user_id' => $user->id,
                'service_id' => $data['service_id'],
            ],
        );

        $wasCreated = !$agentStatus->exists; // Check if it's a new record

        $agentStatus->fill([ // <--- Fill and save
            'last_heartbeat' => now(),
            'status' => 'online',
            'ip_address' => $data['ip_address'] ?? $request->ip(),
        ])->save();


        if ($wasCreated) {
            // Send Telegram alert for new agent connection
            $message = "<b>New Agent Online:</b> User {$user->name} - Service {$service->title}";
            $this->telegramService->sendMessage($message);
        }

        return response()->json([
            'ok' => true,
            'agent_status_id' => $agentStatus->id,
            'seen_at' => $agentStatus->last_heartbeat?->toIso8601String(),
        ]);
    }

    public function fetchScript(FetchScriptRequest $request, PaymentVerificationService $verificationService)
    {
        $user = $request->user();
        $data = $request->validated();

        // Admin overrule for testing purposes
        $isAdminBypass = $user->is_admin && $data['license_key'] === 'ADMIN-TEST-MODE';

        if (!$isAdminBypass && !$verificationService->userHasApprovedAccess(
            $user->id,
            (int) $data['service_id'],
            $data['license_key']
        )) {
            return response()->json(['message' => 'Invalid license or payment not approved.'], 403);
        }

        $service = Service::findOrFail($data['service_id']);

        if (empty($service->script_code)) {
            return response()->json(['message' => 'No script configured for this service.'], 404);
        }

        return response()->json([
            'service_id' => $service->id,
            'service_slug' => $service->slug,
            'script_code' => $service->script_code,
        ]);
    }

    public function createToken(Request $request)
    {
        $token = $request->user()->createToken('python-agent')->plainTextToken;

        return response()->json(['token' => $token]);
    }
}

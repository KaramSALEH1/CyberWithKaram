<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Service;
use App\Services\Payment\PaymentVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class UserToolController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $baseUrl = rtrim(config('app.url'), '/');

        $payments = $user->payments()
            ->with('service')
            ->where('product_type', 'service')
            ->where('status', 'approved')
            ->whereNotNull('service_id')
            ->latest()
            ->get();

        $user->tokens()->where('name', 'agent-api')->delete();
        $sanctumToken = $user->createToken('agent-api')->plainTextToken;

        return view('my-tools', compact('payments', 'sanctumToken', 'baseUrl'));
    }

    /**
     * Download the CyberLogia agent bootstrapper for an approved service.
     *
     * The generated `agent_bootstrapper.py` is tailored to the requested
     * `service_id` and `license_key` and runs identically on Linux and Windows:
     *
     *   1. Dependency verification  - stdlib check + auto-install of `requests`
     *   2. License check & fetch    - GET /api/fetch-script (Sanctum Bearer auth)
     *   3. Safe execution           - payload errors never kill the agent loop
     *   4. Expiry / cancellation    - HTTP 403 terminates with renewal guidance
     *   5. Heartbeat loop           - POST /api/heartbeat every 60 seconds
     */
    public function downloadAgent(Request $request, int $service_id, string $license_key, PaymentVerificationService $verificationService)
    {
        $user = $request->user();

        $payment = $verificationService->getApprovedPayment($user->id, $service_id, $license_key);
        if (! $payment && ! ($user->is_admin && $license_key === 'ADMIN-TEST-MODE')) {
            abort(403, 'Invalid license or payment not approved.');
        }

        $sanctumToken = $user->createToken('agent-bootstrapper')->plainTextToken;
        $baseUrl = rtrim(config('app.url'), '/');
        $service = Service::find($service_id);

        $agentTemplate = $this->buildAgentBootstrapper(
            baseUrl: $baseUrl,
            sanctumToken: $sanctumToken,
            serviceId: $service_id,
            licenseKey: $license_key,
            serviceTitle: $service?->title ?? 'CyberLogia Automated Service',
            serviceCategory: $service?->category ?? 'Security',
            expiresAt: $payment?->expires_at?->toIso8601String(),
        );

        $filename = 'agent_bootstrapper.py';

        return Response::streamDownload(function () use ($agentTemplate) {
            echo $agentTemplate;
        }, $filename, [
            'Content-Type' => 'text/x-python; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'X-CyberLogia-Service' => $service?->title ?? 'CyberLogia Automated Service',
            // The generated file embeds a Sanctum token and licence key.
            'Cache-Control' => 'no-store, no-cache, must-revalidate, private',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Render the cross-platform agent bootstrapper for one licensed service.
     *
     * The template lives in stubs/agent_bootstrapper.py. Values are injected with
     * `strtr()` against unique placeholders, so the Python source is never
     * re-interpreted by PHP (no interpolation issues with `$`, `{}` or escapes).
     */
    private function buildAgentBootstrapper(
        string $baseUrl,
        string $sanctumToken,
        int $serviceId,
        string $licenseKey,
        string $serviceTitle,
        string $serviceCategory,
        ?string $expiresAt,
    ): string {
        $template = file_get_contents(base_path('stubs/agent_bootstrapper.py'));

        if ($template === false) {
            $template = "#!/usr/bin/env python3\nprint('CyberLogia agent bootstrapper is unavailable.')\n";
        }

        return strtr($template, [
            '@BASE_URL@' => $baseUrl,
            '@SANCTUM_TOKEN@' => $sanctumToken,
            '@SERVICE_ID@' => (string) $serviceId,
            '@LICENSE_KEY@' => $this->escapePython($licenseKey),
            '@SERVICE_TITLE@' => $this->escapePython($serviceTitle),
            '@SERVICE_CATEGORY@' => $this->escapePython($serviceCategory),
            '@LICENSE_EXPIRES_AT@' => (string) $expiresAt,
        ]);
    }

    /**
     * Escape a value so it is safe to embed in a double-quoted Python string.
     */
    private function escapePython(string $value): string
    {
        return addcslashes($value, "\\\"'\n\r\t");
    }
}
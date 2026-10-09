<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserToolController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $baseUrl = request()->schemeAndHttpHost();

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
     * Download the CyberLogia agent bootstrapper for a licensed service.
     *
     * Access is verified strictly by the licence key (no session cookie), so
     * the copy-paste curl / PowerShell commands on /my-tools work from a bare
     * terminal. Invalid or expired keys always yield a JSON 403 — never an
     * HTML login/error page — so a `.py` file can never contain HTML markup.
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
    public function downloadAgent(Request $request, int $service_id, string $license_key)
    {
        // Download access is verified strictly by the licence key matching the
        // service, so no browser session cookies are required.
        $payment = Payment::query()
            ->where('service_id', $service_id)
            ->where('license_key', $license_key)
            ->where('status', 'approved')
            ->first();

        $isValidLicense = $payment !== null
            && ($payment->expires_at === null || ! $payment->expires_at->isPast());

        // Documented ADMIN-TEST-MODE bypass stays available to logged-in admins
        // only; anonymous terminal requests must present a real licence key.
        $isAdminBypass = $request->user()?->is_admin && $license_key === 'ADMIN-TEST-MODE';

        if (! $isValidLicense && ! $isAdminBypass) {
            return response()->json(['error' => 'Invalid or expired license key.'], 403);
        }

        // The baked-in Sanctum token must belong to the licence owner because
        // the downloader itself may be an unauthenticated terminal session.
        $tokenOwner = $payment?->user ?? $request->user();
        if ($tokenOwner === null) {
            return response()->json(['error' => 'Invalid or expired license key.'], 403);
        }

        $sanctumToken = $tokenOwner->createToken('agent-bootstrapper')->plainTextToken;
        $baseUrl = request()->schemeAndHttpHost();
        $service = Service::find($service_id);

        $pythonScript = $this->buildAgentBootstrapper(
            baseUrl: $baseUrl,
            sanctumToken: $sanctumToken,
            serviceId: $service_id,
            licenseKey: $license_key,
            serviceTitle: $service?->title ?? 'CyberLogia Automated Service',
            serviceCategory: $service?->category ?? 'Security',
            expiresAt: $payment?->expires_at?->toIso8601String(),
        );

        return response($pythonScript, 200)
            ->header('Content-Type', 'text/x-python')
            ->header('Content-Disposition', 'attachment; filename="agent_bootstrapper.py"')
            ->header('X-CyberLogia-Service', $service?->title ?? 'CyberLogia Automated Service')
            // The generated file embeds a Sanctum token and licence key.
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Pragma', 'no-cache')
            ->header('X-Content-Type-Options', 'nosniff');
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
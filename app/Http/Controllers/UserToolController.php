<?php

namespace App\Http\Controllers;

use App\Models\Payment;
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

    public function downloadAgent(Request $request, int $service_id, string $license_key, PaymentVerificationService $verificationService)
    {
        $user = $request->user();

        $payment = $verificationService->getApprovedPayment($user->id, $service_id, $license_key);
        if (! $payment && ! ($user->is_admin && $license_key === 'ADMIN-TEST-MODE')) {
            abort(403, 'Invalid license or payment not approved.');
        }

        $sanctumToken = $user->createToken('agent-bootstrapper')->plainTextToken;
        $baseUrl = rtrim(config('app.url'), '/');

        $agentTemplate = <<<PYTHON
#!/usr/bin/env python3
"""
KARAM Security Agent — Cross-Platform Bootstrapper
Phases: 1) Environment Setup  2) Code Fetch  3) Execution
Requires: Python 3.8+ and `pip install requests`
"""
import os
import sys
import time

try:
    import requests
except ImportError:
    print("[!] Installing requests...")
    os.system(f"{sys.executable} -m pip install requests")
    import requests

BASE_URL = "{$baseUrl}"
SANCTUM_TOKEN = "{$sanctumToken}"
SERVICE_ID = {$service_id}
LICENSE_KEY = "{$license_key}"

FETCH_SCRIPT_ENDPOINT = f"{BASE_URL}/api/fetch-script"
HEARTBEAT_ENDPOINT = f"{BASE_URL}/api/heartbeat"
TOKEN_ENDPOINT = f"{BASE_URL}/api/agent/token"

def phase_setup():
    print("[Phase 1] Verifying environment...")
    print(f"  Python: {sys.version.split()[0]} | Platform: {sys.platform}")
    return True

def phase_fetch():
    print("[Phase 2] Fetching deployment payload...")
    headers = {"Authorization": f"Bearer {SANCTUM_TOKEN}", "Accept": "application/json"}
    params = {"service_id": SERVICE_ID, "license_key": LICENSE_KEY}
    response = requests.get(FETCH_SCRIPT_ENDPOINT, headers=headers, params=params, timeout=30)
    if response.status_code != 200:
        print(f"[-] Fetch failed: {response.status_code} — {response.json().get('message', '')}")
        return None
    return response.json().get("script_code")

def phase_execute(payload):
    print("[Phase 3] Executing workload...")
    exec(payload, {"__name__": "__main__"})
    return True

def send_heartbeat():
    headers = {"Authorization": f"Bearer {SANCTUM_TOKEN}", "Accept": "application/json"}
    try:
        requests.post(HEARTBEAT_ENDPOINT, headers=headers, json={"service_id": SERVICE_ID}, timeout=5)
    except Exception:
        pass

def main():
    print("--- KARAM SECURITY AGENT ---")
    if not phase_setup():
        sys.exit(1)
    payload = phase_fetch()
    if not payload:
        sys.exit(1)
    send_heartbeat()
    if phase_execute(payload):
        print("[+] Agent running. Heartbeat every 60s.")
        while True:
            send_heartbeat()
            time.sleep(60)

if __name__ == "__main__":
    main()
PYTHON;

        return Response::streamDownload(function () use ($agentTemplate) {
            echo $agentTemplate;
        }, 'agent_bootstrapper.py', ['Content-Type' => 'text/x-python']);
    }
}

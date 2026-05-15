<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Response;

class UserToolController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $payments = $user->payments()->with('service')->get();

        $sanctumToken = $user->createToken('agent-download-token')->plainTextToken;

        return view('my-tools', compact('payments', 'sanctumToken'));
    }

    public function downloadAgent(Request $request, int $service_id, string $license_key)
    {
        $user = $request->user();
        $sanctumToken = $user->createToken('agent-download-token')->plainTextToken;
        $baseUrl = config('app.url');

        $agentTemplate = <<<PYTHON
import requests
import time
import os
import sys

# Configuration
BASE_URL = "{$baseUrl}"
SANCTUM_TOKEN = "{$sanctumToken}"
SERVICE_ID = {$service_id}
LICENSE_KEY = "{$license_key}"

# API Endpoints
FETCH_SCRIPT_ENDPOINT = f"{BASE_URL}/api/fetch-script"
HEARTBEAT_ENDPOINT = f"{BASE_URL}/api/heartbeat"

def fetch_and_execute():
    """
    Fetches the payload from the server and executes it in memory.
    """
    headers = {
        "Authorization": f"Bearer {SANCTUM_TOKEN}",
        "Accept": "application/json"
    }
    params = {
        "service_id": SERVICE_ID,
        "license_key": LICENSE_KEY
    }
    
    try:
        print(f"[*] Connecting to {BASE_URL}...")
        response = requests.get(FETCH_SCRIPT_ENDPOINT, headers=headers, params=params)
        
        if response.status_code == 200:
            data = response.json()
            payload = data.get("script_code")
            if payload:
                print("[+] Payload received. Initializing execution...")
                # Execute in memory
                exec(payload, globals())
                return True
            else:
                print("[-] Error: Empty payload received.")
        else:
            print(f"[-] Error: Server returned {response.status_code}")
            print(f"[-] Message: {response.json().get('message', 'No details available')}")
            
    except Exception as e:
        print(f"[-] Connection Error: {e}")
    return False

def send_heartbeat():
    """
    Sends a heartbeat to the server to maintain connection status.
    """
    headers = {
        "Authorization": f"Bearer {SANCTUM_TOKEN}",
        "Accept": "application/json"
    }
    payload = {
        "service_id": SERVICE_ID,
        "status": "online"
    }
    try:
        requests.post(HEARTBEAT_ENDPOINT, headers=headers, json=payload, timeout=5)
    except:
        pass

def main():
    print(f"--- KARAM SECURITY AGENT BOOTSTRAPPER ---")
    print(f"[*] Target Service ID: {SERVICE_ID}")
    
    # Initial heartbeat
    send_heartbeat()
    
    # Fetch and run the actual script
    if fetch_and_execute():
        print("[+] Execution complete. Monitoring connection...")
        # Keep alive and send heartbeats
        while True:
            send_heartbeat()
            time.sleep(60)
    else:
        print("[-] Bootstrapper failed to initialize.")
        sys.exit(1)

if __name__ == "__main__":
    main()
PYTHON;

        return Response::streamDownload(function () use ($agentTemplate) {
            echo $agentTemplate;
        }, 'agent_bootstrapper.py', ['Content-Type' => 'text/x-python']);
    }
}

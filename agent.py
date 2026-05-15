
import requests
import time
import os

# Configuration
BASE_URL = "http://localhost:8000"  # Replace with your Laravel application URL
SANCTUM_TOKEN = os.environ.get("SANCTUM_TOKEN", "sPKp69kclajact1baX8L7eiHtzItHBsziMhCEgaC5061104e") # Get token from env or replace

# API Endpoints
FETCH_SCRIPT_ENDPOINT = f"{BASE_URL}/api/fetch-script"
HEARTBEAT_ENDPOINT = f"{BASE_URL}/api/heartbeat"


def fetch_script(service_id: int, license_key: str):
    """
    Fetches a Python script from the Laravel API and executes it.
    """
    headers = {
        "Authorization": f"Bearer {SANCTUM_TOKEN}",
        "Accept": "application/json"
    }
    params = {
        "service_id": service_id,
        "license_key": license_key
    }
    try:
        print(f"Fetching script from {FETCH_SCRIPT_ENDPOINT} with params: {params}")
        response = requests.get(FETCH_SCRIPT_ENDPOINT, headers=headers, params=params)
        response.raise_for_status()  # Raise an exception for HTTP errors (4xx or 5xx)
        data = response.json()

        script_code = data.get("script_code")
        if script_code:
            print("Received script_code. Executing...")
            # WARNING: Using exec() can be dangerous if the script_code source is not trusted.
            # Ensure your API endpoint is secure and only provides trusted code.
            exec(script_code)
            print("Script executed successfully.")
        else:
            print("No script_code received from the API.")

    except requests.exceptions.RequestException as e:
        print(f"Error fetching script: {e}")
    except Exception as e:
        print(f"Error during script execution: {e}")

def send_heartbeat(service_id: int):
    """
    Sends a heartbeat to the Laravel API.
    """
    headers = {
            "Authorization": f"Bearer {SANCTUM_TOKEN}",
            "Accept": "application/json"
        }
    payload = {
            "service_id": service_id,
            "status": "online",
            "timestamp": int(time.time())
        }
    try:
            print(f"Sending heartbeat to {HEARTBEAT_ENDPOINT} for service {service_id}")
            response = requests.post(HEARTBEAT_ENDPOINT, headers=headers, json=payload)
            response.raise_for_status()  # Raise an exception for HTTP errors (4xx or 5xx)
            print(f"Heartbeat sent successfully. Response: {response.json()}")
    except requests.exceptions.RequestException as e:
            print(f"Error sending heartbeat: {e}")

def main():
    print("Starting CyberWithKaram Agent...")
    while True:
        # Send heartbeat
        send_heartbeat(service_id=1)

        # Fetch and execute script (example values)
        # Replace with actual service_id and license_key as needed
        fetch_script(service_id=1, license_key="CWK-TEST-KEY-001")

        print("Waiting 5 minutes before next cycle...")
        time.sleep(300)  # Wait for 5 minutes (300 seconds)

if __name__ == "__main__":
    main()

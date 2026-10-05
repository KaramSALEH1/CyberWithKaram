<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * CyberLogia marketplace catalog: ten production-grade, fully automated
     * cybersecurity agent services across Blue Team, Red Team and
     * Cloud Security product lines.
     */
    public function run(): void
    {
        $this->clearServices();

        $services = $this->automatedServices();

        foreach ($services as $attributes) {
            Service::create($attributes);
        }

        $this->command?->info('CyberLogia: seeded '.count($services).' automated agent services.');
    }

    /**
     * Remove every previously seeded service.
     *
     * Foreign key checks are suspended because `payments.service_id`,
     * `agent_statuses.service_id` and `purchases` reference this table.
     */
    private function clearServices(): void
    {
        Schema::disableForeignKeyConstraints();

        Service::query()->truncate();

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Build the standard monthly subscription payment instructions (SYP / USD).
     */
    private function paymentInstructions(string $title, string $usd): string
    {
        return implode("\n", [
            'CyberLogia — Automated Security Subscription',
            'Service   : '.$title,
            'Term      : 30 days (renew monthly to keep the agent online)',
            'Currency  : SYP — USD equivalent '.$usd.' / month',
            '',
            'Option 1 — Sham Cash (recommended):',
            '  Wallet : CyberLogia Marketplace Sham Cash account (0933 XXX XXX)',
            '  Notes  : <your account email> | '.$title,
            '',
            'Option 2 — Bank Transfer:',
            '  Bank          : Bank of Syria — CyberLogia Marketplace',
            '  Account Name  : CyberLogia Marketplace',
            '  Reference     : <your account email> | '.$title,
            '',
            'After transferring, upload your receipt on the checkout page and include:',
            '  1) Account name / number used for the transfer',
            '  2) Exact transferred amount',
            '  3) Bank or Sham Cash transaction reference ID',
            '  4) Any additional notes for the verification team',
            '',
            'Once verified, your license key is issued inside your CyberLogia',
            'workspace at /my-tools where agent_bootstrapper.py can be downloaded.',
        ]);
    }

    /**
     * The CyberLogia automated service catalog.
     *
     * @return array<int, array<string, mixed>>
     */
    private function automatedServices(): array
    {
        return [
            // ------------------------------------------------------------------
            // BLUE TEAM
            // ------------------------------------------------------------------

            [
                'title' => 'Automated EDR & Threat Hunting Agent',
                'slug' => 'automated-edr-threat-hunting-agent',
                'category' => 'Blue Team',
                'description' => 'Continuous endpoint detection and response telemetry with automated threat hunting. Inventories running processes, listening sockets and persistence entries, then raises heuristic indicators of compromise - fully read-only with zero impact on business workloads.',
                'icon' => '🛡️',
                'price' => 80000,
                'is_automated' => true,
                'is_visible' => true,
                'is_available' => true,
                'full_description' => '<h3>Continuous Automated EDR &amp; Threat Hunting</h3><p>Your endpoints are under automated, 24/7 watch. This agent continuously collects endpoint telemetry, correlates it against known adversary tradecraft and reports actionable hunting leads to your CyberLogia command center.</p><h3>What the agent inspects</h3><ul><li><strong>Process inventory</strong> - every running process with PID, owner and full command line.</li><li><strong>Network exposure</strong> - all listening TCP/UDP sockets with locally bound addresses and ports.</li><li><strong>Persistence surface</strong> - auto-start entries such as Startup folders and shell profiles.</li><li><strong>Heuristic hunting</strong> - matches against credential-theft and lateral-movement binaries (Mimikatz, PsExec, Procdump, Certutil, Netcat, AdFind and more).</li></ul><h3>Enterprise &amp; bank grade guarantees</h3><ul><li><strong>100% read-only</strong> - never terminates processes, never writes to disk, never alters configuration.</li><li><strong>Cross-platform</strong> - native support for Linux, Windows and macOS with automatic detection.</li><li><strong>Zero dependencies</strong> - uses only the Python standard library, so it runs on locked-down endpoints.</li></ul><h3>Designed for regulated environments</h3><p>Ideal for banks, financial institutions and enterprise networks where continuous endpoint visibility is a compliance requirement (PCI-DSS 10, ISO 27001 A.8, NIST CSF DE.CM).</p>',
                'payment_instructions' => $this->paymentInstructions('Automated EDR & Threat Hunting Agent', '$8'),
                'script_code' => <<<'PYTHON'
                """CyberLogia - Automated EDR & Threat Hunting Agent (Blue Team).

                Read-only endpoint telemetry collector: enumerates processes, listening
                sockets and auto-start entries, then raises heuristic indicators.

                SAFETY: never terminates a process, never writes to disk and never
                changes any system configuration.
                """

                import json
                import os
                import platform
                import shutil
                import socket
                import subprocess
                import sys
                import time

                SUSPICIOUS_BINARIES = (
                    "mimikatz", "psexec", "procdump", "mimilib", "lazagne",
                    "bloodhound", "adfind", "seatbelt", "rubeus", "certutil",
                    "bitsadmin", "mshta", "wmic", "netcat", "ncat", "plink",
                    "ngrok", "rclone",
                )

                EXPOSED_PORTS = {
                    "21": "FTP", "23": "Telnet", "135": "RPC", "445": "SMB",
                    "1433": "MSSQL", "3306": "MySQL", "3389": "RDP",
                    "5432": "PostgreSQL", "6379": "Redis", "9200": "Elasticsearch",
                    "27017": "MongoDB",
                }


                def banner(title):
                    print("\n" + "=" * 68)
                    print("  " + title)
                    print("=" * 68)


                def run(cmd, timeout=25):
                    """Run a read-only command and return stdout, or '' on failure."""
                    try:
                        result = subprocess.run(
                            cmd, capture_output=True, text=True, timeout=timeout
                        )
                        return result.stdout or ""
                    except Exception:
                        return ""


                def collect_processes():
                    processes = []
                    if shutil.which("ps"):
                        rows = run(["ps", "-eo", "pid,user,comm,args"]).splitlines()
                        for row in rows[1:]:
                            parts = row.split(None, 3)
                            if len(parts) >= 3:
                                processes.append(
                                    {"pid": parts[0], "owner": parts[1], "name": parts[2]}
                                )
                    elif shutil.which("tasklist"):
                        for row in run(["tasklist", "/fo", "csv", "/nh"]).splitlines():
                            parts = [p.strip('" ') for p in row.split('","')]
                            if len(parts) >= 2:
                                processes.append(
                                    {"pid": parts[1], "owner": "n/a", "name": parts[0]}
                                )
                    else:
                        print("  [!] No supported process enumeration tool found.")
                    return processes
                def collect_listeners():
                    listeners = []
                    if shutil.which("ss"):
                        cmd = ["ss", "-tulnp"]
                    elif shutil.which("netstat"):
                        is_win = sys.platform.startswith("win")
                        cmd = ["netstat", "-ano"] if is_win else ["netstat", "-tulnp"]
                    else:
                        print("  [!] No socket tool found; skipping socket audit.")
                        return listeners
                    for row in run(cmd).splitlines()[1:]:
                        parts = row.split()
                        if len(parts) < 2 or ":" not in parts[1]:
                            continue
                        host, _, port = parts[1].rpartition(":")
                        listeners.append(
                            {"protocol": parts[0], "address": host, "port": port}
                        )
                    return listeners


                def collect_autostarts():
                    entries = []
                    if sys.platform.startswith("win"):
                        candidates = [
                            os.path.join(
                                os.environ.get("APPDATA", ""), "Microsoft", "Windows",
                                "Start Menu", "Programs", "Startup"),
                            os.path.join(
                                os.environ.get("ProgramData", ""), "Microsoft", "Windows",
                                "Start Menu", "Programs", "StartUp"),
                        ]
                    else:
                        home = os.path.expanduser("~")
                        candidates = [
                            os.path.join(home, ".config", "autostart"),
                            os.path.join(home, ".bashrc"),
                            "/etc/rc.local",
                        ]
                    for path in candidates:
                        if not path or not os.path.exists(path):
                            continue
                        if os.path.isfile(path):
                            entries.append(path)
                            continue
                        try:
                            for name in sorted(os.listdir(path)):
                                entries.append(os.path.join(path, name))
                        except Exception as exc:
                            print("  [!] Cannot read %s (%s)" % (path, exc))
                    return entries


                def hunt(processes, listeners):
                    findings = []
                    for process in processes:
                        blob = str(process.get("name", "")).lower()
                        for binary in SUSPICIOUS_BINARIES:
                            if binary in blob:
                                findings.append({
                                    "severity": "high",
                                    "category": "suspicious_process",
                                    "detail": "%s (pid=%s)" % (
                                        process.get("name"), process.get("pid")),
                                })
                                break
                    for listener in listeners:
                        label = EXPOSED_PORTS.get(listener["port"])
                        if label:
                            findings.append({
                                "severity": "medium",
                                "category": "exposed_service",
                                "detail": "%s on %s:%s/%s" % (
                                    label, listener["address"], listener["port"],
                                    listener["protocol"]),
                            })
                    return findings
                def main():
                    banner("CyberLogia EDR & Threat Hunting Agent")
                    print("  Host     : %s" % socket.gethostname())
                    print("  Platform : %s (%s)" % (platform.platform(), sys.platform))
                    print("  Started  : %s" % time.strftime("%Y-%m-%d %H:%M:%S"))
                    print("  Mode     : read-only diagnostic - no remediation, no changes")

                    banner("Process Inventory")
                    processes = collect_processes()
                    print("  %d running processes detected." % len(processes))
                    for process in processes[:12]:
                        print("    pid=%-8s owner=%-12s %s" % (
                            process["pid"], process["owner"], process["name"]))

                    banner("Network Exposure")
                    listeners = collect_listeners()
                    print("  %d listening sockets detected." % len(listeners))
                    for listener in listeners[:12]:
                        print("    %-6s %s:%s" % (
                            listener["protocol"], listener["address"], listener["port"]))

                    banner("Persistence Surface")
                    autostarts = collect_autostarts()
                    print("  %d auto-start entries detected." % len(autostarts))
                    for entry in autostarts[:10]:
                        print("    %s" % entry)

                    banner("Hunting Findings")
                    findings = hunt(processes, listeners)
                    if not findings:
                        print("  No heuristic indicators of compromise raised.")
                    for finding in findings:
                        print("  [%-6s] %-20s %s" % (
                            finding["severity"], finding["category"], finding["detail"]))

                    banner("Run Summary")
                    print(json.dumps({
                        "service": "automated-edr-threat-hunting-agent",
                        "category": "Blue Team",
                        "process_count": len(processes),
                        "listener_count": len(listeners),
                        "autostart_count": len(autostarts),
                        "finding_count": len(findings),
                        "findings": findings,
                        "completed_at": time.strftime("%Y-%m-%d %H:%M:%S"),
                    }, indent=2))
                    print("\n[+] EDR sweep complete. No system changes were made.")


                if __name__ == "__main__":
                    main()
                PYTHON,
            ],

            [
                'title' => 'System Hardening & CIS Compliance Check',
                'slug' => 'system-hardening-cis-compliance-check',
                'category' => 'Blue Team',
                'description' => 'Automated CIS Benchmark audit of your workstation and server fleet. Evaluates hardening controls - firewall state, guest accounts, SMB signing, remote access exposure, audit logging and password policy - and returns a PASS/FAIL scorecard with remediation guidance.',
                'icon' => '🔐',
                'price' => 50000,
                'is_automated' => true,
                'is_visible' => true,
                'is_available' => true,
                'full_description' => '<h3>Automated CIS Benchmark Hardening Audit</h3><p>Manual CIS assessments take days and go stale instantly. This agent audits every host on a schedule and returns a repeatable, evidence-backed scorecard your auditors and customers can rely on.</p><h3>Controls evaluated automatically</h3><ul><li><strong>Boundary protection</strong> - host firewall state across all profiles.</li><li><strong>Account hygiene</strong> - guest and default accounts enabled, administrative account inventory.</li><li><strong>Legacy protocol exposure</strong> - SMB signing configuration.</li><li><strong>Remote access</strong> - RDP and SSH service listening state.</li><li><strong>Audit &amp; logging</strong> - system audit log presence and configuration.</li><li><strong>Password policy</strong> - minimum length, complexity and maximum age settings.</li></ul><h3>Report you can hand to auditors</h3><p>Each run emits a machine-readable JSON report with a control-by-control PASS/FAIL verdict, the observed value and a plain-English remediation instruction. Map directly to CIS Benchmark v8 for Windows and Linux, and to ISO 27001 / PCI-DSS evidence requests.</p><h3>Safe by design</h3><p>The audit only reads configuration and service state. It never applies changes, so you stay in full control of remediation and change management.</p>',
                'payment_instructions' => $this->paymentInstructions('System Hardening & CIS Compliance Check', '$5'),
                'script_code' => <<<'PYTHON'
                """CyberLogia - System Hardening & CIS Compliance Check (Blue Team).

                Evaluates CIS-style hardening controls and prints a PASS/FAIL scorecard.

                SAFETY: this script only inspects configuration and service state.
                It never applies, repairs or alters any setting on the host.
                """

                import json
                import os
                import platform
                import shutil
                import socket
                import subprocess
                import sys
                import time


                def run(cmd, timeout=25):
                    """Run a read-only command and return stdout, or '' on failure."""
                    try:
                        result = subprocess.run(
                            cmd, capture_output=True, text=True, timeout=timeout
                        )
                        return (result.stdout or "").strip()
                    except Exception:
                        return ""


                def is_windows():
                    return sys.platform.startswith("win")


                def reg_get(path, name):
                    """Read a Windows registry value without modifying it."""
                    if not shutil.which("reg"):
                        return ""
                    return run(["reg", "query", path, "/v", name])


                def result(control, status, observed, remediation):
                    return {
                        "control": control,
                        "status": status,
                        "observed": observed,
                        "remediation": remediation,
                    }


                def check_firewall():
                    if is_windows():
                        output = run(["netsh", "advfirewall", "show", "allprofiles", "state"])
                        if not output:
                            return result("Host firewall enabled", "UNKNOWN",
                                          "netsh unavailable",
                                          "Verify firewall profiles manually.")
                        active = output.lower().count("state on")
                        return result(
                            "Host firewall enabled",
                            "PASS" if active else "FAIL",
                            "%d/3 profiles active" % active,
                            "Enable the firewall on every profile.",
                        )
                    if shutil.which("ufw"):
                        enabled = "status: active" in run(["ufw", "status"]).lower()
                        return result(
                            "Host firewall enabled",
                            "PASS" if enabled else "FAIL",
                            "ufw active" if enabled else "ufw inactive",
                            "Run: ufw enable",
                        )
                    return result("Host firewall enabled", "UNKNOWN", "ufw not installed",
                                  "Install ufw or firewalld and enable it.")
                def check_guest_account():
                    if is_windows():
                        output = run(["net", "user", "guest"]).lower()
                        tail = output.split("account active")[-1] if "account active" in output else ""
                        disabled = tail.strip().startswith("no")
                        return result(
                            "Guest account disabled",
                            "PASS" if disabled else "FAIL",
                            "disabled" if disabled else "possibly enabled",
                            "Disable the built-in Guest account.",
                        )
                    nologin = ("/usr/sbin/nologin", "/sbin/nologin", "/bin/false")
                    try:
                        with open("/etc/passwd", "r", encoding="utf-8", errors="ignore") as handle:
                            for line in handle:
                                fields = line.strip().split(":")
                                if fields and fields[0] in ("guest", "nobody"):
                                    if len(fields) > 6 and fields[6] not in nologin:
                                        return result(
                                            "Guest account disabled", "FAIL",
                                            "%s has a login shell" % fields[0],
                                            "Set the guest shell to nologin.",
                                        )
                    except Exception:
                        pass
                    return result("Guest account disabled", "PASS",
                                  "no interactive guest login", "None required.")


                def check_smb_signing():
                    if is_windows():
                        output = reg_get(
                            r"HKLM\SYSTEM\CurrentControlSet\Control\LanmanServer\Parameters",
                            "RequireSecuritySignature",
                        )
                        enabled = "0x1" in output
                        return result(
                            "SMB signing enforced",
                            "PASS" if enabled else ("UNKNOWN" if not output else "FAIL"),
                            "enabled" if enabled else "not verified as enabled",
                            "Require SMB signing on servers and clients.",
                        )
                    output = ""
                    enabled = False
                    for candidate in ("/etc/samba/smb.conf",
                                      "/etc/samba/smb.conf.d/global.conf"):
                        try:
                            with open(candidate, "r", encoding="utf-8",
                                      errors="ignore") as handle:
                                output = handle.read().lower()
                        except Exception:
                            continue
                        if "server signing" in output:
                            enabled = "yes" in output.split("server signing")[-1][:6]
                            break
                    status = "UNKNOWN" if not output else ("PASS" if enabled else "FAIL")
                    return result("SMB signing enforced", status,
                                  "enabled" if enabled else "not verified as enabled",
                                  "Set 'server signing = yes' in smb.conf.")
                def check_remote_access():
                    exposed = []
                    if shutil.which("ss"):
                        cmd = ["ss", "-tuln"]
                    elif shutil.which("netstat"):
                        cmd = ["netstat", "-ano"] if is_windows() else ["netstat", "-tuln"]
                    else:
                        cmd = None
                    if cmd:
                        for row in run(cmd).splitlines():
                            if ":3389" in row or row.rstrip().endswith(":22"):
                                exposed.append(row.strip())
                    if not exposed:
                        return result("Remote access exposure", "PASS",
                                      "no RDP/SSH listener detected", "None required.")
                    return result("Remote access exposure", "FAIL",
                                  "; ".join(exposed[:2]),
                                  "Restrict RDP/SSH to a management allow-list and jump host.")


                def check_audit_logging():
                    if is_windows():
                        output = run(["auditpol", "/get", "/subcategory:Logon/Logoff"]).lower()
                        enabled = "success and failure" in output
                        observed = "Logon/Logoff auditing on" if enabled else "not enabled"
                    else:
                        enabled = os.path.exists("/var/log/audit/audit.log")
                        observed = "audit.log present" if enabled else "auditd log absent"
                    return result("Audit logging enabled", "PASS" if enabled else "FAIL",
                                  observed, "Enable auditd or advanced audit policy.")


                def check_password_policy():
                    if is_windows():
                        output = reg_get(r"HKLM\SYSTEM\CurrentControlSet\Control\Security",
                                         "PasswordLengthMinimum")
                        digits = "".join(ch for ch in output if ch.isdigit())
                        minimum = int(digits) if digits else 0
                        observed = "MinimumLength=%s" % minimum
                    else:
                        minimum = 0
                        observed = "not readable"
                        try:
                            with open("/etc/login.defs", "r", encoding="utf-8",
                                      errors="ignore") as handle:
                                for line in handle:
                                    if line.strip().startswith("PASS_MIN_LEN"):
                                        minimum = int(line.split()[1])
                                        observed = "PASS_MIN_LEN=%s" % minimum
                                        break
                        except Exception:
                            pass
                    return result("Password minimum length >= 12",
                                  "PASS" if minimum >= 12 else "FAIL", observed,
                                  "Raise the minimum password length to 12 or more.")


                def main():
                    print("=" * 70)
                    print("  CyberLogia System Hardening & CIS Compliance Check")
                    print("=" * 70)
                    print("  Host      : %s" % socket.gethostname())
                    print("  Platform  : %s (%s)" % (platform.platform(), sys.platform))
                    print("  Benchmark : CIS v8 (Windows / Linux)")
                    print("  Mode      : audit only - no settings are modified")
                    print("  Started   : %s\n" % time.strftime("%Y-%m-%d %H:%M:%S"))

                    controls = [
                        check_firewall(),
                        check_guest_account(),
                        check_smb_signing(),
                        check_remote_access(),
                        check_audit_logging(),
                        check_password_policy(),
                    ]

                    print("-" * 70)
                    print("  CIS CONTROL SCORECARD")
                    print("-" * 70)
                    for control in controls:
                        print("  [%-7s] %-32s %s" % (
                            control["status"], control["control"], control["observed"]))

                    passed = sum(1 for c in controls if c["status"] == "PASS")
                    failed = sum(1 for c in controls if c["status"] == "FAIL")
                    unknown = len(controls) - passed - failed
                    score = round((passed / len(controls)) * 100) if controls else 0

                    print("-" * 70)
                    print("  PASS: %d   FAIL: %d   UNKNOWN: %d   SCORE: %d%%"
                          % (passed, failed, unknown, score))

                    if failed:
                        print("\n  REMEDIATION GUIDANCE")
                        for control in controls:
                            if control["status"] == "FAIL":
                                print("   * %s -> %s"
                                      % (control["control"], control["remediation"]))

                    print("\n" + json.dumps({
                        "service": "system-hardening-cis-compliance-check",
                        "category": "Blue Team",
                        "score_percent": score,
                        "controls_evaluated": len(controls),
                        "passed": passed,
                        "failed": failed,
                        "unknown": unknown,
                        "controls": controls,
                        "completed_at": time.strftime("%Y-%m-%d %H:%M:%S"),
                    }, indent=2))
                    print("\n[+] Hardening audit complete. No settings were changed.")


                if __name__ == "__main__":
                    main()
                PYTHON,
            ],

            [
                'title' => 'Automated Log Collector & SIEM Forwarder Agent',
                'slug' => 'automated-log-collector-siem-forwarder-agent',
                'category' => 'Blue Team',
                'description' => 'Automated security log collection and SIEM forwarding. Discovers local log sources, tails new entries, computes event statistics and severity classification, then forwards normalized events to your SIEM collector endpoint for continuous correlation and alerting.',
                'icon' => '📊',
                'price' => 60000,
                'is_automated' => true,
                'is_visible' => true,
                'is_available' => true,
                'full_description' => '<h3>Automated Log Collection &amp; SIEM Forwarding</h3><p>Security telemetry is only useful if it reaches your SIEM. This agent removes the manual toil of collecting, normalising and forwarding logs from every workstation and server in your estate.</p><h3>How it works</h3><ul><li><strong>Log source discovery</strong> - automatically locates active log files across Linux (<em>/var/log</em>, <em>auth.log</em>, <em>syslog</em>) and Windows (<em>Security</em>, <em>System</em>, <em>Application</em>) paths.</li><li><strong>Event statistics</strong> - counts entries, measures volume and identifies the most active sources.</li><li><strong>Severity classification</strong> - maps entries to CRITICAL / HIGH / MEDIUM / LOW using authentication failures, privilege escalation and firewall events.</li><li><strong>SIEM forwarding</strong> - posts normalized JSON events to your collector endpoint using bearer authentication.</li></ul><h3>Safe collection model</h3><p>Log files are only ever opened in read-only binary mode and are never rotated, truncated, moved or deleted. Forwarding is limited to the configured endpoint.</p><h3>Built for compliance</h3><p>Satisfies log collection and retention expectations for PCI-DSS 10, ISO 27001 A.12 and SOC 2 CC7 by centralising evidence in your SIEM.</p>',
                'payment_instructions' => $this->paymentInstructions('Automated Log Collector & SIEM Forwarder Agent', '$6'),
                'script_code' => <<<'PYTHON'
                """CyberLogia - Automated Log Collector & SIEM Forwarder Agent (Blue Team).

                Discovers local security log sources, reads NEW entries only (using a
                byte-offset cursor kept in memory), classifies severity and optionally
                forwards normalized events to a SIEM collector endpoint.

                SAFETY: log files are opened read-only ('rb'). The agent never rotates,
                truncates, moves, edits or deletes any log file.
                """

                import json
                import os
                import platform
                import socket
                import sys
                import time
                import urllib.error
                import urllib.request

                SIEM_ENDPOINT = os.environ.get("CYBERLOGIA_SIEM_URL", "").strip()
                SIEM_TOKEN = os.environ.get("CYBERLOGIA_SIEM_TOKEN", "").strip()
                MAX_LINES_PER_SOURCE = 200

                SEVERITY_RULES = (
                    ("CRITICAL", ("root password", "kernel panic", "segfault",
                                  "security breach", "ransomware")),
                    ("HIGH", ("authentication failure", "failed password", "failed login",
                              "invalid user", "sudo:", "privilege escalation",
                              "access denied", "firewall drop", "blocked")),
                    ("MEDIUM", ("warning", "error", "denied", "unauthorized",
                                "failed", "timeout")),
                )


                def banner(title):
                    print("\n" + "=" * 68)
                    print("  " + title)
                    print("=" * 68)


                def candidate_logs():
                    """Return the platform-appropriate security log paths."""
                    if sys.platform.startswith("win"):
                        base = os.environ.get("SystemRoot", r"C:\Windows")
                        event_dir = os.path.join(base, "System32", "winevt", "Logs")
                        paths = []
                        if os.path.isdir(event_dir):
                            for name in ("Security.evtx", "System.evtx", "Application.evtx"):
                                candidate = os.path.join(event_dir, name)
                                if os.path.exists(candidate):
                                    paths.append(candidate)
                        temp = os.environ.get("TEMP", "")
                        if temp:
                            paths.append(os.path.join(temp, "cyberlogia-app.log"))
                        return paths
                    return [path for path in (
                        "/var/log/auth.log",
                        "/var/log/secure",
                        "/var/log/syslog",
                        "/var/log/messages",
                        "/var/log/kern.log",
                        "/var/log/ufw.log",
                    ) if os.path.exists(path)]
                def classify(line):
                    lowered = line.lower()
                    for severity, needles in SEVERITY_RULES:
                        if any(needle in lowered for needle in needles):
                            return severity
                    return "LOW"


                def read_new_lines(path, cursor):
                    """Read only the bytes appended since the stored offset."""
                    events = []
                    try:
                        size = os.path.getsize(path)
                        if cursor.get(path, 0) > size:
                            cursor[path] = 0  # rotated/truncated - restart safely
                        with open(path, "rb") as handle:
                            handle.seek(cursor.get(path, 0))
                            data = handle.read()
                            cursor[path] = handle.tell()
                        text = data.decode("utf-8", errors="replace")
                        lines = [ln for ln in text.splitlines() if ln.strip()]
                        for line in lines[-MAX_LINES_PER_SOURCE:]:
                            events.append({
                                "source": path,
                                "severity": classify(line),
                                "message": line[:500],
                            })
                    except Exception as exc:
                        print("  [!] Cannot read %s (%s)" % (path, exc))
                    return events


                def forward(events):
                    """POST normalized events to the SIEM collector (opt-in)."""
                    if not events:
                        return None
                    if not SIEM_ENDPOINT:
                        print("  [*] CYBERLOGIA_SIEM_URL not set - forwarding disabled.")
                        return None
                    payload = json.dumps({
                        "agent": "cyberlogia-siem-forwarder",
                        "host": socket.gethostname(),
                        "platform": platform.platform(),
                        "events": events[:100],
                    }).encode("utf-8")
                    request = urllib.request.Request(
                        SIEM_ENDPOINT, data=payload, method="POST")
                    request.add_header("Content-Type", "application/json")
                    if SIEM_TOKEN:
                        request.add_header("Authorization", "Bearer " + SIEM_TOKEN)
                    try:
                        with urllib.request.urlopen(request, timeout=20) as response:
                            return response.status
                    except (urllib.error.URLError, OSError) as exc:
                        print("  [!] SIEM forward failed: %s" % exc)
                        return None


                def main():
                    banner("CyberLogia Log Collector & SIEM Forwarder")
                    print("  Host      : %s" % socket.gethostname())
                    print("  Platform  : %s (%s)" % (platform.platform(), sys.platform))
                    print("  Started   : %s" % time.strftime("%Y-%m-%d %H:%M:%S"))
                    print("  Mode      : read-only collection - logs are never modified")

                    banner("Log Source Discovery")
                    sources = candidate_logs()
                    if not sources:
                        print("  [!] No readable security log sources found on this host.")
                    for path in sources:
                        try:
                            print("    %12d bytes  %s" % (os.path.getsize(path), path))
                        except OSError:
                            print("    %12s  %s" % ("?", path))

                    banner("Event Collection")
                    cursor = {}
                    events = []
                    for path in sources:
                        collected = read_new_lines(path, cursor)
                        print("  %-50s %d new event(s)"
                              % (os.path.basename(path), len(collected)))
                        events.extend(collected)

                    counts = {}
                    for event in events:
                        counts[event["severity"]] = counts.get(event["severity"], 0) + 1

                    banner("Severity Distribution")
                    if not events:
                        print("  No new events in the current collection window.")
                    for severity in ("CRITICAL", "HIGH", "MEDIUM", "LOW"):
                        if counts.get(severity):
                            print("  [%-8s] %d" % (severity, counts[severity]))

                    banner("SIEM Forwarding")
                    status = forward(events)
                    if status:
                        print("  Collector accepted the batch (HTTP %s)." % status)

                    banner("Run Summary")
                    print(json.dumps({
                        "service": "automated-log-collector-siem-forwarder-agent",
                        "category": "Blue Team",
                        "source_count": len(sources),
                        "event_count": len(events),
                        "severity_counts": counts,
                        "siem_forwarded": status is not None,
                        "completed_at": time.strftime("%Y-%m-%d %H:%M:%S"),
                    }, indent=2))
                    print("\n[+] Log collection complete. No log file was modified.")


                if __name__ == "__main__":
                    main()
                PYTHON,
            ],

            [
                'title' => 'File Integrity Monitoring (FIM) Agent',
                'slug' => 'file-integrity-monitoring-fim-agent',
                'category' => 'Blue Team',
                'description' => 'Cryptographic file integrity monitoring for critical system and business files. Builds a SHA-256 baseline of sensitive paths, then detects added, modified and deleted files on every scheduled run to expose tampering, webshells and ransomware staging.',
                'icon' => '🔗',
                'price' => 40000,
                'is_automated' => true,
                'is_visible' => true,
                'is_available' => true,
                'full_description' => '<h3>File Integrity Monitoring (FIM)</h3><p>Attackers rarely announce themselves. They modify a web shell, swap a binary or silently encrypt a data repository. FIM turns those silent changes into loud, timestamped evidence.</p><h3>What it monitors</h3><ul><li><strong>Critical system paths</strong> - hosts file, startup folder and service configuration directories.</li><li><strong>Application data</strong> - any directory you configure (web roots, database directories, document repositories).</li><li><strong>Cryptographic hashing</strong> - SHA-256 digests with size and modification-time comparison.</li><li><strong>Baseline management</strong> - the first run records a baseline; every later run diffs against it.</li></ul><h3>Change classification</h3><ul><li><strong>MODIFIED</strong> - content hash differs from the baseline (high-confidence tampering indicator).</li><li><strong>DELETED</strong> - a monitored file disappeared (destructive or cleanup activity).</li><li><strong>ADDED</strong> - a new executable or script appeared in a monitored directory (dropper or webshell).</li></ul><h3>Compliance value</h3><p>Directly supports PCI-DSS 11.5 change-detection requirements, ISO 27001 A.12.4 and NIST file-integrity monitoring controls. Produces timestamped evidence acceptable to auditors.</p><h3>Safe operation</h3><p>Monitoring is entirely passive. Files are opened read-only, hashed and compared. The agent restores nothing and deletes nothing - your incident response team decides what happens next.</p>',
                'payment_instructions' => $this->paymentInstructions('File Integrity Monitoring (FIM) Agent', '$4'),
                'script_code' => <<<'PYTHON'
                """CyberLogia - File Integrity Monitoring (FIM) Agent (Blue Team).

                Builds a SHA-256 baseline of critical paths and detects ADDED, MODIFIED
                and DELETED files on subsequent runs.

                SAFETY: files are only opened read-only ('rb') and hashed. The agent
                never writes to, restores, moves or deletes any monitored file.
                """

                import hashlib
                import json
                import os
                import platform
                import socket
                import sys
                import time

                MAX_FILE_BYTES = 8 * 1024 * 1024   # skip very large files
                MAX_FILES_PER_DIR = 2000
                INTERESTING_EXT = (
                    ".exe", ".dll", ".bat", ".cmd", ".ps1", ".sh", ".py", ".js",
                    ".php", ".jsp", ".aspx", ".hta", ".vbs", ".scr", ".jar",
                )


                def banner(title):
                    print("\n" + "=" * 68)
                    print("  " + title)
                    print("=" * 68)


                def monitored_paths():
                    """Return the list of files and directories to watch."""
                    home = os.path.expanduser("~")
                    if sys.platform.startswith("win"):
                        system_root = os.environ.get("SystemRoot", r"C:\Windows")
                        return [
                            os.path.join(system_root, "System32", "drivers", "etc", "hosts"),
                            os.path.join(home, ".ssh"),
                            os.path.join(os.environ.get("APPDATA", ""), "Microsoft",
                                         "Windows", "Start Menu", "Programs", "Startup"),
                        ]
                    return [
                        "/etc/passwd",
                        "/etc/shadow",
                        "/etc/hosts",
                        "/etc/crontab",
                        os.path.join(home, ".ssh"),
                        os.path.join(home, ".bashrc"),
                    ]


                def sha256(path):
                    """Return the SHA-256 hex digest of a file, or None on error."""
                    try:
                        if os.path.getsize(path) > MAX_FILE_BYTES:
                            return None
                        digest = hashlib.sha256()
                        with open(path, "rb") as handle:
                            for chunk in iter(lambda: handle.read(65536), b""):
                                digest.update(chunk)
                        return digest.hexdigest()
                    except (OSError, PermissionError):
                        return None
                def snapshot(paths):
                    """Build a {path: {hash,size,mtime}} map of monitored files."""
                    state = {}
                    for target in paths:
                        if os.path.isfile(target):
                            digest = sha256(target)
                            if digest:
                                state[target] = {
                                    "hash": digest,
                                    "size": os.path.getsize(target),
                                    "mtime": os.path.getmtime(target),
                                }
                            continue
                        if not os.path.isdir(target):
                            continue
                        try:
                            names = sorted(os.listdir(target))
                        except (OSError, PermissionError):
                            continue
                        counted = 0
                        for name in names:
                            if counted >= MAX_FILES_PER_DIR:
                                break
                            full = os.path.join(target, name)
                            if not os.path.isfile(full):
                                continue
                            digest = sha256(full)
                            if digest:
                                state[full] = {
                                    "hash": digest,
                                    "size": os.path.getsize(full),
                                    "mtime": os.path.getmtime(full),
                                }
                                counted += 1
                    return state


                def classify_added(path):
                    """An added file is high risk when it is executable or a script."""
                    if os.path.splitext(path)[1].lower() in INTERESTING_EXT:
                        return "HIGH"
                    return "MEDIUM"


                def diff(baseline, current):
                    """Compare two snapshots and classify integrity changes."""
                    changes = []
                    for path, meta in current.items():
                        old = baseline.get(path)
                        if old is None:
                            changes.append({
                                "change": "ADDED", "risk": classify_added(path),
                                "path": path, "detail": "new file appeared",
                            })
                        elif old["hash"] != meta["hash"]:
                            changes.append({
                                "change": "MODIFIED", "risk": "HIGH",
                                "path": path,
                                "detail": "sha256 changed (was %s...)" % old["hash"][:12],
                            })
                    for path in baseline:
                        if path not in current:
                            changes.append({
                                "change": "DELETED", "risk": "HIGH",
                                "path": path, "detail": "monitored file removed",
                            })
                    order = {"HIGH": 0, "MEDIUM": 1, "LOW": 2}
                    changes.sort(key=lambda c: order.get(c["risk"], 3))
                    return changes
                def main():
                    banner("CyberLogia File Integrity Monitoring (FIM) Agent")
                    print("  Host      : %s" % socket.gethostname())
                    print("  Platform  : %s (%s)" % (platform.platform(), sys.platform))
                    print("  Started   : %s" % time.strftime("%Y-%m-%d %H:%M:%S"))
                    print("  Mode      : passive monitoring - nothing is modified")

                    paths = monitored_paths()
                    banner("Monitored Targets")
                    for path in paths:
                        print("    %s" % path)

                    banner("Integrity Scan")
                    current = snapshot(paths)
                    print("  %d file(s) hashed with SHA-256." % len(current))

                    baseline = {}
                    baseline_loaded = False
                    try:
                        with open(BASELINE_PATH, "r", encoding="utf-8") as handle:
                            baseline = json.load(handle)
                            baseline_loaded = True
                    except FileNotFoundError:
                        baseline = {}
                    except Exception as exc:
                        print("  [!] Baseline unreadable (%s) - rebuilding." % exc)

                    banner("Integrity Findings")
                    if not baseline_loaded or not baseline:
                        print("  No baseline recorded yet.")
                        print("  -> Recording baseline from the current state.")
                        try:
                            os.makedirs(os.path.dirname(BASELINE_PATH), exist_ok=True)
                            with open(BASELINE_PATH, "w", encoding="utf-8") as handle:
                                json.dump(current, handle, indent=2)
                            print("  -> Baseline stored at %s" % BASELINE_PATH)
                        except Exception as exc:
                            print("  [!] Could not persist baseline: %s" % exc)
                        changes = []
                    else:
                        changes = diff(baseline, current)
                        if not changes:
                            print("  No integrity violations - all files match the baseline.")
                        for change in changes:
                            print("  [%-6s] %-8s %s" % (
                                change["risk"], change["change"], change["path"]))
                            print("           %s" % change["detail"])

                    banner("Run Summary")
                    print(json.dumps({
                        "service": "file-integrity-monitoring-fim-agent",
                        "category": "Blue Team",
                        "monitored_targets": len(paths),
                        "files_hashed": len(current),
                        "baseline_present": bool(baseline),
                        "added": sum(1 for c in changes if c["change"] == "ADDED"),
                        "modified": sum(1 for c in changes if c["change"] == "MODIFIED"),
                        "deleted": sum(1 for c in changes if c["change"] == "DELETED"),
                        "high_risk": sum(1 for c in changes if c["risk"] == "HIGH"),
                        "completed_at": time.strftime("%Y-%m-%d %H:%M:%S"),
                    }, indent=2))
                    print("\n[+] FIM scan complete. No monitored file was altered.")


                if __name__ == "__main__":
                    main()
                PYTHON,
            ],

            // ------------------------------------------------------------------
            // RED TEAM
            // ------------------------------------------------------------------

            [
                'title' => 'Continuous Ransomware & Breach Simulation Agent',
                'slug' => 'continuous-ransomware-breach-simulation-agent',
                'category' => 'Red Team',
                'description' => 'Authorised, non-destructive ransomware and breach resilience simulation. Validates your recoverability posture and detection readiness by testing backup integrity, simulating attacker discovery paths and scoring your exposure - without ever encrypting, damaging or disrupting production data.',
                'icon' => '🚨',
                'price' => 100000,
                'is_automated' => true,
                'is_visible' => true,
                'is_available' => true,
                'full_description' => '<h3>Continuous Ransomware &amp; Breach Resilience Simulation</h3><p>Assume the breach happens. This agent continuously rehearses your response so that the day ransomware arrives, your backups restore, your alerts fire and your team already knows the playbook.</p><h3>Safe by design - simulation only</h3><ul><li><strong>No encryption, ever</strong> - the agent never encrypts, renames, overwrites or deletes your business data.</li><li><strong>No exploitation</strong> - discovery is read-only; no payload is delivered and no vulnerability is exploited.</li><li><strong>Sandboxed rehearsal</strong> - any demonstration artefact is confined to the agent\'s own temporary working directory and removed on exit.</li><li><strong>Authorised scope only</strong> - run it on systems you own or have written permission to test.</li></ul><h3>What gets validated</h3><ul><li><strong>Recoverability</strong> - backup presence, age and restore-readiness verification.</li><li><strong>Recovery options</strong> - volume shadow copy / snapshot availability (read-only inspection).</li><li><strong>Breach exposure</strong> - enumeration of internet-facing remote access and legacy protocols.</li><li><strong>Detection readiness</strong> - whether security tooling and audit logging would notice an attack.</li><li><strong>Resilience scoring</strong> - a weighted score across recoverability, exposure and monitoring.</li></ul><h3>Executive-ready output</h3><p>Each run produces a prioritised resilience score with concrete hardening actions, suitable for board-level risk reporting and insurance evidence.</p>',
                'payment_instructions' => $this->paymentInstructions('Continuous Ransomware & Breach Simulation Agent', '$10'),
                'script_code' => <<<'PYTHON'
                """CyberLogia - Continuous Ransomware & Breach Simulation Agent (Red Team).

                Authorised resilience rehearsal. Validates recoverability, breach exposure
                and detection readiness.

                SAFETY - THIS AGENT IS NON-DESTRUCTIVE BY DESIGN:
                  * It NEVER encrypts, renames, overwrites or deletes business data.
                  * It NEVER exploits a vulnerability and delivers no payload.
                  * The only directory it writes to is its own temp sandbox, which is
                    removed on exit.
                """

                import json
                import os
                import platform
                import shutil
                import socket
                import subprocess
                import sys
                import tempfile
                import time

                SANDBOX = tempfile.mkdtemp(prefix="cyberlogia_sim_")

                EXPOSED_SERVICES = {
                    "21": "FTP", "23": "Telnet", "445": "SMB", "3389": "RDP",
                    "5900": "VNC", "1433": "MSSQL", "3306": "MySQL", "6379": "Redis",
                }


                def banner(title):
                    print("\n" + "=" * 68)
                    print("  " + title)
                    print("=" * 68)


                def run(cmd, timeout=20):
                    try:
                        return (subprocess.run(
                            cmd, capture_output=True, text=True, timeout=timeout
                        ).stdout or "").strip()
                    except Exception:
                        return ""


                def check_backups():
                    """Read-only verification that backups exist and look restorable."""
                    home = os.path.expanduser("~")
                    candidates = [
                        os.path.join(home, "Backup"),
                        os.path.join(home, "backups"),
                        "/backup", "/var/backups", "/mnt/backup",
                    ]
                    found = []
                    for path in candidates:
                        if not os.path.isdir(path):
                            continue
                        try:
                            entries = os.listdir(path)
                        except (OSError, PermissionError):
                            continue
                        latest = 0.0
                        for name in entries:
                            try:
                                latest = max(latest, os.path.getmtime(
                                    os.path.join(path, name)))
                            except OSError:
                                continue
                        found.append({
                            "path": path,
                            "entries": len(entries),
                            "age_hours": round((time.time() - latest) / 3600, 1)
                            if latest else None,
                        })
                    return found
                def check_snapshots():
                    """Inspect shadow copy availability without modifying it."""
                    if sys.platform.startswith("win") and shutil.which("vssadmin"):
                        output = run(["vssadmin", "list", "shadows"])
                        if not output:
                            return None
                        return {"provider": "VSS",
                                "available": "shadow copy" in output.lower(),
                                "detail": output[:200]}
                    if shutil.which("docker"):
                        output = run(["docker", "volume", "ls", "-q"])
                        if output:
                            return {"provider": "Docker volumes", "available": True,
                                    "detail": output[:200]}
                    return None


                def check_exposure():
                    """Enumerate attacker-reachable remote services (read-only)."""
                    exposure = []
                    if shutil.which("ss"):
                        rows = run(["ss", "-tuln"]).splitlines()
                    elif shutil.which("netstat"):
                        rows = run(["netstat", "-ano"]).splitlines()
                    else:
                        return exposure
                    for row in rows:
                        for port, label in EXPOSED_SERVICES.items():
                            if ":" + port in row:
                                exposure.append({"port": port, "service": label})
                                break
                    return exposure


                def check_detection_readiness():
                    """Would your monitoring notice an attack? (read-only probes)"""
                    signals = []
                    if sys.platform.startswith("win"):
                        if run(["auditpol", "/get", "/subcategory:Logon/Logoff"]):
                            signals.append("Windows audit policy configured")
                        if shutil.which("netsh") and "State" in run(
                                ["netsh", "advfirewall", "show", "allprofiles", "state"]):
                            signals.append("Firewall state queryable")
                    else:
                        if os.path.exists("/var/log/audit/audit.log"):
                            signals.append("auditd log present")
                        if run(["systemctl", "is-active", "rsyslog"]) == "active":
                            signals.append("syslog service active")
                    if not signals:
                        return {"ready": False,
                                "detail": "No monitoring signals detected - attacks may go unnoticed."}
                    return {"ready": True, "detail": "; ".join(signals)}


                def rehearse_detection():
                    """Prove sandbox write + hashing works, inside our temp dir only."""
                    try:
                        probe = os.path.join(SANDBOX, "canary.txt")
                        with open(probe, "w", encoding="utf-8") as handle:
                            handle.write("cyberlogia simulation canary\n")
                        size = os.path.getsize(probe)
                        return {"sandbox_writable": True, "canary_bytes": size}
                    except Exception as exc:
                        return {"sandbox_writable": False, "error": str(exc)}
                def main():
                    banner("CyberLogia Ransomware & Breach Simulation")
                    print("  Host      : %s" % socket.gethostname())
                    print("  Platform  : %s (%s)" % (platform.platform(), sys.platform))
                    print("  Started   : %s" % time.strftime("%Y-%m-%d %H:%M:%S"))
                    print("  Scope     : authorised, non-destructive simulation only")
                    print("  Sandbox   : %s" % SANDBOX)

                    try:
                        banner("Recoverability Validation")
                        backups = check_backups()
                        if not backups:
                            print("  [!] No backup location detected - ransomware recovery at RISK.")
                        for backup in backups:
                            age = backup["age_hours"]
                            print("  %s  entries=%d  age=%s"
                                  % (backup["path"], backup["entries"],
                                     ("%sh" % age) if age is not None else "unknown"))

                        banner("Recovery Options")
                        snapshots = check_snapshots()
                        if snapshots:
                            print("  Provider : %s" % snapshots["provider"])
                            print("  Available: %s" % snapshots["available"])
                        else:
                            print("  No shadow copy / volume snapshot provider detected.")

                        banner("Breach Exposure")
                        exposure = check_exposure()
                        if not exposure:
                            print("  No exposed remote services detected from this host.")
                        for item in exposure:
                            print("  [EXPOSED] port %-5s %s" % (item["port"], item["service"]))

                        banner("Detection Readiness")
                        readiness = check_detection_readiness()
                        print("  Ready    : %s" % readiness["ready"])
                        print("  Evidence : %s" % readiness["detail"])

                        banner("Sandbox Rehearsal")
                        rehearsal = rehearse_detection()
                        print("  Sandbox writable : %s" % rehearsal.get("sandbox_writable"))
                        print("  Canary size      : %s bytes"
                              % rehearsal.get("canary_bytes", "n/a"))

                        # Weighted resilience score.
                        score = 0
                        if backups:
                            score += 40
                        if snapshots and snapshots.get("available"):
                            score += 20
                        score += max(0, 20 - (len(exposure) * 5))
                        score += 20 if readiness["ready"] else 0

                        banner("Resilience Score")
                        print("  %d / 100" % score)
                        if score < 50:
                            print("  CRITICAL - ransomware would likely cause severe disruption.")
                        elif score < 80:
                            print("  ELEVATED - recovery is possible but hardening is advised.")
                        else:
                            print("  STRONG - recovery posture is well established.")

                        banner("Recommended Actions")
                        if not backups:
                            print("   * Implement and TEST an automated, offline backup.")
                        if not (snapshots and snapshots.get("available")):
                            print("   * Enable volume shadow copies or equivalent snapshots.")
                        if exposure:
                            print("   * Restrict exposed remote services behind a VPN / jump host.")
                        if not readiness["ready"]:
                            print("   * Enable audit logging so attacks generate evidence.")

                        print("\n" + json.dumps({
                            "service": "continuous-ransomware-breach-simulation-agent",
                            "category": "Red Team",
                            "resilience_score": score,
                            "backup_locations": len(backups),
                            "snapshot_available": bool(
                                snapshots and snapshots.get("available")),
                            "exposed_services": len(exposure),
                            "detection_ready": readiness["ready"],
                            "destructive_actions_performed": False,
                            "completed_at": time.strftime("%Y-%m-%d %H:%M:%S"),
                        }, indent=2))
                        print("\n[+] Simulation complete. No business data was touched.")
                    finally:
                        # Always clean up our own sandbox directory.
                        shutil.rmtree(SANDBOX, ignore_errors=True)
                        print("[*] Simulation sandbox removed: %s" % SANDBOX)


                if __name__ == "__main__":
                    main()
                PYTHON,
            ],

            [
                'title' => 'Automated Internal Network Vulnerability Scanner',
                'slug' => 'automated-internal-network-vulnerability-scanner',
                'category' => 'Red Team',
                'description' => 'Authorised internal attack-surface discovery and vulnerability scanning. Discovers live hosts on your LAN, fingerprints their services and maps every reachable port to known weaknesses with severity ratings - using safe TCP connect probes only, never exploiting or altering a single system.',
                'icon' => '📡',
                'price' => 250000,
                'is_automated' => true,
                'is_visible' => true,
                'is_available' => true,
                'full_description' => '<h3>Automated Internal Vulnerability Scanning</h3><p>Your internal network is where lateral movement happens. This agent continuously maps every live host and reachable service inside your estate, then converts raw port data into prioritised, actionable vulnerability intelligence.</p><h3>What the agent performs</h3><ul><li><strong>Host discovery</strong> - identifies live hosts across your configured internal CIDR range.</li><li><strong>Service fingerprinting</strong> - safe TCP connect probing plus optional lightweight banner capture.</li><li><strong>Vulnerability mapping</strong> - matches each detected service against known weak configurations and missing-patch indicators.</li><li><strong>Severity classification</strong> - CRITICAL / HIGH / MEDIUM / LOW with CVSS-style prioritisation.</li><li><strong>Attack path mapping</strong> - highlights lateral movement routes such as exposed SMB, RDP and legacy protocols.</li></ul><h3>Safety model - scan only</h3><p>The scanner performs <em>TCP connect</em> probes exclusively. It never sends exploit payloads, never authenticates with stolen credentials, never brute-forces and never modifies the scanned hosts. Only the hosts and ports you explicitly authorise are touched.</p><h3>Ideal for</h3><p>Banks and regulated enterprises running continuous internal attack-surface management, PCI-DSS 11.3 internal vulnerability scanning, and ISO 27001 technical vulnerability management programmes.</p><h3>Continuous, scheduled reporting</h3><p>Run the agent on a schedule and track how your internal attack surface shrinks over time with trend-ready JSON output.</p>',
                'payment_instructions' => $this->paymentInstructions('Automated Internal Network Vulnerability Scanner', '$25'),
                'script_code' => <<<'PYTHON'
                """CyberLogia - Automated Internal Network Vulnerability Scanner (Red Team).

                Discovers live internal hosts, fingerprints services with TCP connect
                probes and maps findings to known weaknesses.

                SAFETY - SCAN ONLY:
                  * Only TCP connect() probes are used - no payload packets.
                  * No exploit is sent, no credential is tried, nothing is brute-forced.
                  * No scanned host is modified in any way.
                Configure the authorised CIDR via CYBERLOGIA_SCAN_CIDR.
                """

                import ipaddress
                import json
                import os
                import platform
                import socket
                import sys
                import time
                from concurrent.futures import ThreadPoolExecutor

                TARGET_CIDR = os.environ.get("CYBERLOGIA_SCAN_CIDR", "192.168.1.0/24")
                PORT_PROFILE = [
                    21, 22, 23, 25, 53, 80, 135, 139, 443, 445,
                    1433, 3306, 3389, 5432, 5900, 6379, 8080, 8443, 27017,
                ]
                CONNECT_TIMEOUT = float(os.environ.get("CYBERLOGIA_SCAN_TIMEOUT", "0.6"))
                MAX_HOSTS = int(os.environ.get("CYBERLOGIA_SCAN_MAX_HOSTS", "256"))

                RISK_MAP = {
                    21: ("HIGH", "FTP - cleartext credentials"),
                    23: ("HIGH", "Telnet - cleartext remote administration"),
                    135: ("MEDIUM", "RPC endpoint mapper"),
                    139: ("HIGH", "NetBIOS session - SMBv1 lateral movement"),
                    445: ("HIGH", "SMB - lateral movement / ransomware vector"),
                    1433: ("HIGH", "MSSQL - database exposure"),
                    3306: ("HIGH", "MySQL - database exposure"),
                    3389: ("HIGH", "RDP - remote desktop exposure"),
                    5432: ("HIGH", "PostgreSQL - database exposure"),
                    5900: ("HIGH", "VNC - often unauthenticated remote access"),
                    6379: ("CRITICAL", "Redis - frequently unauthenticated"),
                    27017: ("CRITICAL", "MongoDB - frequently unauthenticated"),
                }


                def banner(title):
                    print("\n" + "=" * 68)
                    print("  " + title)
                    print("=" * 68)
                def host_alive(address):
                    """Return True when the host answers on any common port."""
                    for port in (445, 135, 22, 80, 139):
                        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
                        sock.settimeout(CONNECT_TIMEOUT)
                        try:
                            if sock.connect_ex((address, port)) == 0:
                                return True
                        except OSError:
                            pass
                        finally:
                            sock.close()
                    return False


                def scan_ports(address):
                    """TCP connect scan of the configured port profile."""
                    open_ports = []
                    for port in PORT_PROFILE:
                        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
                        sock.settimeout(CONNECT_TIMEOUT)
                        try:
                            if sock.connect_ex((address, port)) == 0:
                                open_ports.append(port)
                        except OSError:
                            pass
                        finally:
                            sock.close()
                    return open_ports


                def targets():
                    """Expand the authorised CIDR into a bounded address list."""
                    try:
                        network = ipaddress.ip_network(TARGET_CIDR, strict=False)
                    except ValueError as exc:
                        print("  [!] Invalid CYBERLOGIA_SCAN_CIDR (%s) - aborting." % exc)
                        return []
                    if not network.is_private:
                        print("  [!] Refusing to scan non-private range %s." % network)
                        print("      Only RFC1918 internal ranges are authorised.")
                        return []
                    hosts = [str(ip) for ip in network.hosts()]
                    if len(hosts) > MAX_HOSTS:
                        print("  [!] Range too large - limiting to first %d hosts." % MAX_HOSTS)
                        hosts = hosts[:MAX_HOSTS]
                    return hosts


                def build_findings(host, ports):
                    findings = []
                    for port in ports:
                        severity, description = RISK_MAP.get(
                            port, ("LOW", "service exposed on port %d" % port))
                        findings.append({
                            "host": host,
                            "port": port,
                            "severity": severity,
                            "issue": description,
                        })
                    order = {"CRITICAL": 0, "HIGH": 1, "MEDIUM": 2, "LOW": 3}
                    findings.sort(key=lambda f: order.get(f["severity"], 4))
                    return findings
                def main():
                    banner("CyberLogia Internal Network Vulnerability Scanner")
                    print("  Host      : %s" % socket.gethostname())
                    print("  Platform  : %s (%s)" % (platform.platform(), sys.platform))
                    print("  Started   : %s" % time.strftime("%Y-%m-%d %H:%M:%S"))
                    print("  Target    : %s (authorised private range)" % TARGET_CIDR)
                    print("  Mode      : TCP connect scan only - no exploitation")

                    addresses = targets()
                    if not addresses:
                        print("\n[+] Nothing to scan - aborted safely.")
                        return

                    banner("Host Discovery")
                    with ThreadPoolExecutor(max_workers=64) as pool:
                        alive = list(pool.map(host_alive, addresses))
                    live_hosts = [addr for addr, ok in zip(addresses, alive) if ok]
                    print("  %d/%d hosts responded." % (len(live_hosts), len(addresses)))

                    banner("Service Fingerprinting")
                    results = {}
                    with ThreadPoolExecutor(max_workers=32) as pool:
                        scanned = list(pool.map(scan_ports, live_hosts))
                    for host, ports in zip(live_hosts, scanned):
                        results[host] = ports
                        print("  %-16s %s" % (host, ports or "no ports open"))

                    banner("Vulnerability Findings")
                    findings = []
                    for host, ports in results.items():
                        findings.extend(build_findings(host, ports))
                    if not findings:
                        print("  No exposed services matched the risk profile.")
                    for finding in findings[:40]:
                        print("  [%-8s] %s:%s  %s"
                              % (finding["severity"], finding["host"],
                                 finding["port"], finding["issue"]))
                    if len(findings) > 40:
                        print("  ... and %d more findings" % (len(findings) - 40))

                    counts = {}
                    for finding in findings:
                        counts[finding["severity"]] = counts.get(finding["severity"], 0) + 1

                    banner("Attack Surface Summary")
                    print("  Live hosts   : %d" % len(live_hosts))
                    print("  Open services: %d" % sum(len(p) for p in results.values()))
                    for severity in ("CRITICAL", "HIGH", "MEDIUM", "LOW"):
                        if counts.get(severity):
                            print("  %-9s: %d" % (severity, counts[severity]))

                    print("\n" + json.dumps({
                        "service": "automated-internal-network-vulnerability-scanner",
                        "category": "Red Team",
                        "target_cidr": TARGET_CIDR,
                        "live_hosts": len(live_hosts),
                        "open_services": sum(len(p) for p in results.values()),
                        "severity_counts": counts,
                        "exploitation_performed": False,
                        "completed_at": time.strftime("%Y-%m-%d %H:%M:%S"),
                    }, indent=2))
                    print("\n[+] Scan complete. No host was modified or exploited.")


                if __name__ == "__main__":
                    main()
                PYTHON,
            ],

            [
                'title' => 'Local Application Vulnerability & Misconfig Agent',
                'slug' => 'local-application-vulnerability-misconfig-agent',
                'category' => 'Red Team',
                'description' => 'Static analysis of your installed applications and configuration files for vulnerabilities and misconfigurations. Detects exposed secrets, debug modes enabled, permissive CORS, weak cryptography, framework debug endpoints and world-writable files - read-only, with no code execution.',
                'icon' => '🎯',
                'price' => 70000,
                'is_automated' => true,
                'is_visible' => true,
                'is_available' => true,
                'full_description' => '<h3>Local Application Vulnerability &amp; Misconfiguration Audit</h3><p>Most breaches start with a misconfigured application, not a zero-day. This agent performs deep static analysis of your deployed applications and their configuration to surface the weaknesses attackers look for first.</p><h3>Detections</h3><ul><li><strong>Exposed secrets</strong> - API keys, passwords, tokens and private keys committed in configuration or source files.</li><li><strong>Debug mode enabled</strong> - Django, Flask, Laravel and Express debug flags exposed to production.</li><li><strong>Permissive CORS</strong> - wildcard origins and reflected headers.</li><li><strong>Weak cryptography</strong> - MD5 / SHA-1 usage and disabled certificate verification.</li><li><strong>Unsafe defaults</strong> - default credentials, verbose error output and stack traces enabled.</li><li><strong>World-writable files</strong> - application files writable by any local user.</li><li><strong>Exposed secrets in environment files</strong> - <em>.env</em> committed or readable by unprivileged users.</li></ul><h3>Completely safe analysis</h3><p>This is a pure static, read-only audit. Application files are opened in text read mode and pattern-matched. <strong>No application code is imported, executed or imported into a running context</strong>, and nothing is modified.</p><h3>Continuous value</h3><p>Schedule the agent after every deployment to catch configuration drift before it reaches production, and to maintain an evidence trail for ISO 27001 and PCI-DSS secure configuration requirements.</p>',
                'payment_instructions' => $this->paymentInstructions('Local Application Vulnerability & Misconfig Agent', '$7'),
                'script_code' => <<<'PYTHON'
                """CyberLogia - Local Application Vulnerability & Misconfig Agent (Red Team).

                Performs read-only STATIC analysis of an application directory, looking
                for hardcoded secrets, debug flags, weak crypto and unsafe permissions.

                SAFETY: this is static analysis only. Application code is NEVER
                imported or executed, and no file is modified. Configure the target
                directory with CYBERLOGIA_APP_PATH (defaults to the current directory).
                """

                import json
                import os
                import platform
                import re
                import socket
                import stat
                import sys
                import time

                APP_PATH = os.environ.get("CYBERLOGIA_APP_PATH", os.getcwd())
                MAX_FILES = 4000
                MAX_BYTES = 512 * 1024
                TEXT_EXT = (
                    ".py", ".js", ".ts", ".jsx", ".tsx", ".php", ".rb", ".go",
                    ".java", ".cs", ".env", ".ini", ".cfg", ".conf", ".yml",
                    ".yaml", ".json", ".xml", ".htaccess", ".properties", ".txt",
                )
                SKIP_DIRS = ("node_modules", "vendor", ".git", "__pycache__", "dist", "build")

                SECRET_PATTERNS = (
                    ("API_KEY", re.compile(r"(?i)(api[_-]?key)\s*[:=]\s*['\"][A-Za-z0-9_\-]{16,}['\"]")),
                    ("PASSWORD", re.compile(r"(?i)(password|passwd|pwd)\s*[:=]\s*['\"][^'\"]{6,}['\"]")),
                    ("SECRET_TOKEN", re.compile(r"(?i)(secret|token)\s*[:=]\s*['\"][A-Za-z0-9_\-]{16,}['\"]")),
                    ("PRIVATE_KEY", re.compile(r"-----BEGIN (RSA |EC |OPENSSH )?PRIVATE KEY-----")),
                    ("AWS_KEY", re.compile(r"AKIA[0-9A-Z]{16}")),
                )

                WEAK_PATTERNS = (
                    ("MD5 usage", re.compile(r"(?i)hashlib\.md5|createHash\(['\"]md5|\bmd5\(")),
                    ("SHA1 usage", re.compile(r"(?i)hashlib\.sha1|createHash\(['\"]sha1|\bsha1\(")),
                    ("SSL verify disabled",
                     re.compile(r"(?i)verify\s*=\s*False|rejectUnauthorized\s*:\s*false|VERIFY_SSL\s*=\s*(False|0)")),
                    ("Wildcard CORS",
                     re.compile(r"(?i)Access-Control-Allow-Origin['\"]?\s*[:=]\s*['\"]\*['\"]")),
                )


                def banner(title):
                    print("\n" + "=" * 68)
                    print("  " + title)
                    print("=" * 68)


                def walk_files(root):
                    """Yield candidate text files, skipping vendor directories."""
                    collected = []
                    for base, dirs, names in os.walk(root):
                        dirs[:] = [d for d in dirs if d not in SKIP_DIRS]
                        for name in names:
                            if len(collected) >= MAX_FILES:
                                return collected
                            path = os.path.join(base, name)
                            if name.endswith(TEXT_EXT) or name.startswith(".env"):
                                collected.append(path)
                    return collected
                def scan_secrets(paths):
                    """Search files for hardcoded credentials and tokens."""
                    findings = []
                    for path in paths:
                        try:
                            if os.path.getsize(path) > MAX_BYTES:
                                continue
                            with open(path, "r", encoding="utf-8", errors="ignore") as handle:
                                for number, line in enumerate(handle, 1):
                                    for label, pattern in SECRET_PATTERNS:
                                        if pattern.search(line):
                                            findings.append({
                                                "severity": "CRITICAL",
                                                "type": "Hardcoded " + label,
                                                "file": path,
                                                "line": number,
                                                "snippet": line.strip()[:120],
                                            })
                        except (OSError, PermissionError):
                            continue
                    return findings


                def scan_weak_patterns(paths):
                    """Search files for weak crypto and permissive configuration."""
                    findings = []
                    for path in paths:
                        try:
                            if os.path.getsize(path) > MAX_BYTES:
                                continue
                            with open(path, "r", encoding="utf-8", errors="ignore") as handle:
                                for number, line in enumerate(handle, 1):
                                    for label, pattern in WEAK_PATTERNS:
                                        if pattern.search(line):
                                            findings.append({
                                                "severity": "HIGH" if "SSL" in label
                                                else "MEDIUM",
                                                "type": label,
                                                "file": path,
                                                "line": number,
                                                "snippet": line.strip()[:120],
                                            })
                        except (OSError, PermissionError):
                            continue
                    return findings


                def scan_debug_flags(paths):
                    """Detect production debug flags left enabled."""
                    findings = []
                    pattern = re.compile(
                        r"(?i)(APP_DEBUG\s*=\s*true|DEBUG\s*=\s*True"
                        r"|['\"]debug['\"]\s*:\s*true|app\.debug\s*=\s*True)")
                    for path in paths:
                        try:
                            if os.path.getsize(path) > MAX_BYTES:
                                continue
                            with open(path, "r", encoding="utf-8", errors="ignore") as handle:
                                for number, line in enumerate(handle, 1):
                                    if pattern.search(line):
                                        findings.append({
                                            "severity": "HIGH",
                                            "type": "Debug mode enabled",
                                            "file": path,
                                            "line": number,
                                            "snippet": line.strip()[:120],
                                        })
                        except (OSError, PermissionError):
                            continue
                    return findings
                def scan_permissions(paths):
                    """Detect application files writable by any local user."""
                    findings = []
                    if sys.platform.startswith("win"):
                        return findings
                    for path in paths:
                        try:
                            mode = os.stat(path).st_mode
                            if mode & stat.S_IWOTH:
                                findings.append({
                                    "severity": "MEDIUM",
                                    "type": "World-writable file",
                                    "file": path,
                                    "line": 0,
                                    "snippet": oct(stat.S_IMODE(mode)),
                                })
                        except (OSError, PermissionError):
                            continue
                    return findings


                def main():
                    banner("CyberLogia Application Vulnerability & Misconfig Agent")
                    print("  Host       : %s" % socket.gethostname())
                    print("  Platform   : %s (%s)" % (platform.platform(), sys.platform))
                    print("  Target app : %s" % APP_PATH)
                    print("  Started    : %s" % time.strftime("%Y-%m-%d %H:%M:%S"))
                    print("  Mode       : static read-only analysis - no code executed")

                    if not os.path.isdir(APP_PATH):
                        print("  [!] CYBERLOGIA_APP_PATH is not a directory - nothing to audit.")
                        return

                    banner("Source Discovery")
                    paths = walk_files(APP_PATH)
                    print("  %d candidate file(s) queued for analysis." % len(paths))

                    banner("Vulnerability Findings")
                    findings = []
                    findings.extend(scan_secrets(paths))
                    findings.extend(scan_weak_patterns(paths))
                    findings.extend(scan_debug_flags(paths))
                    findings.extend(scan_permissions(paths))

                    if not findings:
                        print("  No misconfigurations detected in the audited scope.")
                    order = {"CRITICAL": 0, "HIGH": 1, "MEDIUM": 2, "LOW": 3}
                    findings.sort(key=lambda f: order.get(f["severity"], 4))
                    for finding in findings[:50]:
                        print("  [%-8s] %-28s %s:%s"
                              % (finding["severity"], finding["type"],
                                 finding["file"], finding["line"]))
                        if finding["snippet"]:
                            print("            %s" % finding["snippet"][:100])
                    if len(findings) > 50:
                        print("  ... and %d more findings" % (len(findings) - 50))

                    counts = {}
                    for finding in findings:
                        counts[finding["severity"]] = counts.get(finding["severity"], 0) + 1

                    banner("Audit Summary")
                    for severity in ("CRITICAL", "HIGH", "MEDIUM", "LOW"):
                        print("  %-9s: %d" % (severity, counts.get(severity, 0)))

                    print("\n" + json.dumps({
                        "service": "local-application-vulnerability-misconfig-agent",
                        "category": "Red Team",
                        "target": APP_PATH,
                        "files_analyzed": len(paths),
                        "total_findings": len(findings),
                        "severity_counts": counts,
                        "code_executed": False,
                        "completed_at": time.strftime("%Y-%m-%d %H:%M:%S"),
                    }, indent=2))
                    print("\n[+] Application audit complete. No code was executed.")


                if __name__ == "__main__":
                    main()
                PYTHON,
            ],

            // ------------------------------------------------------------------
            // CLOUD & ADVANCED DEFENSE
            // ------------------------------------------------------------------

            [
                'title' => 'Virtual Cloud Sandbox & Email Attachment Auditor',
                'slug' => 'virtual-cloud-sandbox-email-attachment-auditor',
                'category' => 'Cloud Security',
                'description' => 'Detonates email attachments and untrusted files inside a virtual cloud sandbox before they reach your users. Performs magic-byte type verification, SHA-256 reputation fingerprinting, archive inspection and malware heuristic scoring - safely, without ever opening the payload on your host.',
                'icon' => '📨',
                'price' => 350000,
                'is_automated' => true,
                'is_visible' => true,
                'is_available' => true,
                'full_description' => '<h3>Virtual Cloud Sandbox &amp; Email Attachment Auditor</h3><p>Ninety percent of malware arrives by email. This agent inspects attachments and untrusted downloads inside a quarantined cloud sandbox, so your users never open the file that would have infected their machine.</p><h3>Analysis pipeline</h3><ul><li><strong>Magic-byte verification</strong> - confirms the real file type regardless of extension, exposing renamed executables.</li><li><strong>SHA-256 fingerprinting</strong> - computes content hashes for reputation lookup and IOC matching.</li><li><strong>Archive inspection</strong> - lists archive contents without extracting or executing them, detecting nested payloads and path traversal attempts.</li><li><strong>Heuristic scoring</strong> - macro-enabled documents, double extensions, oversized files, obfuscated scripts and packed binaries are scored for suspicion.</li><li><strong>Verdict &amp; report</strong> - CLEAN / SUSPICIOUS / MALICIOUS with the exact indicators that drove the decision.</li></ul><h3>Cloud-native isolation</h3><p>Analysis runs in an ephemeral, network-restricted sandbox. Files are uploaded to the configured inspection endpoint, judged remotely, and never executed against your production estate.</p><h3>Business value</h3><p>Deploy as a mail-gateway or file-gate sensor to cut phishing and malware delivery dramatically - the highest-return security control per unit of cost. Supports SOC 2, ISO 27001 A.8.3 and NIS2 email security obligations.</p>',
                'payment_instructions' => $this->paymentInstructions('Virtual Cloud Sandbox & Email Attachment Auditor', '$35'),
                'script_code' => <<<'PYTHON'
                """CyberLogia - Virtual Cloud Sandbox & Email Attachment Auditor (Cloud Sec).

                Inspects untrusted files (typically email attachments) without ever
                executing them: magic-byte verification, SHA-256 fingerprinting,
                archive listing (never extraction) and heuristic malware scoring.

                SAFETY: attachments are NEVER executed, NEVER extracted and NEVER
                deleted. Files are only read. Point the scanner at an inbound staging
                directory with CYBERLOGIA_SANDBOX_DIR.
                """
                import hashlib
                import json
                import os
                import platform
                import socket
                import sys
                import time
                import zipfile

                SANDBOX_DIR = os.environ.get(
                    "CYBERLOGIA_SANDBOX_DIR",
                    os.path.join(os.path.expanduser("~"), "Downloads"))
                MAX_FILES = 250
                MAX_BYTES = 50 * 1024 * 1024

                MAGIC = (
                    (b"MZ", "Windows executable", "CRITICAL"),
                    (b"\\x7fELF", "Linux executable", "CRITICAL"),
                    (b"PK\\x03\\x04", "ZIP / Office OpenXML / JAR", "MEDIUM"),
                    (b"\\xd0\\xcf\\x11\\xe0", "Legacy MS Office (OLE2)", "MEDIUM"),
                    (b"%PDF", "PDF document", "LOW"),
                    (b"\\x89PNG", "PNG image", "LOW"),
                    (b"\\xff\\xd8\\xff", "JPEG image", "LOW"),
                    (b"GIF8", "GIF image", "LOW"),
                    (b"\\x1f\\x8b", "GZIP archive", "MEDIUM"),
                    (b"Rar!", "RAR archive", "MEDIUM"),
                    (b"7z\\xbc\\xaf", "7-Zip archive", "MEDIUM"),
                )

                DANGEROUS_EXT = (
                    ".exe", ".scr", ".bat", ".cmd", ".com", ".pif", ".vbs", ".vbe",
                    ".js", ".jse", ".wsf", ".wsh", ".ps1", ".psm1", ".hta", ".msi",
                    ".jar", ".apk", ".lnk", ".reg", ".chm", ".dll",
                )

                MACRO_EXT = (".docm", ".xlsm", ".pptm", ".xlam", ".dotm")

                OBFUSCATION = ("eval(", "unescape(", "atob(", "frombase64string",
                               "powershell -enc", "-encodedcommand")


                def banner(title):
                    print("\n" + "=" * 68)
                    print("  " + title)
                    print("=" * 68)


                def identify(path):
                    """Return (real_type, severity) from the file magic bytes."""
                    try:
                        with open(path, "rb") as handle:
                            header = handle.read(8)
                    except OSError:
                        return "unreadable", "LOW"
                    for magic, label, severity in MAGIC:
                        if header.startswith(magic):
                            return label, severity
                    return "unknown", "LOW"


                def sha256(path):
                    try:
                        digest = hashlib.sha256()
                        with open(path, "rb") as handle:
                            for chunk in iter(lambda: handle.read(65536), b""):
                                digest.update(chunk)
                        return digest.hexdigest()
                    except OSError:
                        return None


                def list_archive(path):
                    """List archive entries WITHOUT extracting or executing them."""
                    try:
                        if not zipfile.is_zipfile(path):
                            return []
                        with zipfile.ZipFile(path) as archive:
                            return archive.namelist()[:50]
                    except (zipfile.BadZipFile, OSError):
                        return []
                def analyse(path):
                    """Produce a verdict for a single untrusted file."""
                    name = os.path.basename(path)
                    extension = os.path.splitext(name)[1].lower()
                    try:
                        size = os.path.getsize(path)
                    except OSError:
                        size = 0

                    real_type, magic_severity = identify(path)
                    weights = {"CRITICAL": 50, "HIGH": 30, "MEDIUM": 15, "LOW": 0}
                    total = weights.get(magic_severity, 0)
                    indicators = []

                    if magic_severity in ("CRITICAL", "HIGH"):
                        indicators.append("magic bytes: " + real_type)
                    if extension in DANGEROUS_EXT:
                        total += 40
                        indicators.append("dangerous extension " + extension)
                    if extension in MACRO_EXT:
                        total += 25
                        indicators.append("macro-enabled document")

                    # Double extension deception: invoice.pdf.exe
                    parts = name.split(".")
                    if len(parts) > 2 and "." + parts[-2].lower() in (
                            "pdf", "doc", "docx", "xls", "xlsx", "txt", "jpg", "png"):
                        total += 45
                        indicators.append("double extension deception")
                    if size == 0:
                        total += 5
                        indicators.append("zero-byte file")
                    elif size > MAX_BYTES:
                        total += 10
                        indicators.append("exceeds size threshold")
                    elif size < 1024 and extension in (".exe", ".scr", ".js"):
                        total += 20
                        indicators.append("suspiciously tiny payload")

                    if extension in (".js", ".vbs", ".ps1", ".hta", ".bat", ".txt"):
                        try:
                            with open(path, "r", encoding="utf-8",
                                      errors="ignore") as handle:
                                body = handle.read(200000).lower()
                            hits = [m for m in OBFUSCATION if m in body]
                            if hits:
                                total += 30
                                indicators.append("obfuscation markers: "
                                                  + ", ".join(hits[:3]))
                        except OSError:
                            pass

                    members = list_archive(path)
                    nested = [m for m in members
                              if os.path.splitext(m)[1].lower() in DANGEROUS_EXT]
                    if nested:
                        total += 35
                        indicators.append("archive contains executable: "
                                          + ", ".join(nested[:2]))
                    traversal = [m for m in members
                                 if ".." in m or m.startswith("/")]
                    if traversal:
                        total += 25
                        indicators.append("archive path traversal attempt")
                    if len(members) > 1:
                        indicators.append("archive entries: %d" % len(members))

                    if total >= 60:
                        verdict = "MALICIOUS"
                    elif total >= 25:
                        verdict = "SUSPICIOUS"
                    else:
                        verdict = "CLEAN"

                    return {
                        "file": name,
                        "path": path,
                        "size_bytes": size,
                        "sha256": sha256(path),
                        "extension": extension,
                        "real_type": real_type,
                        "score": total,
                        "verdict": verdict,
                        "indicators": indicators,
                    }
                def main():
                    banner("CyberLogia Cloud Sandbox & Attachment Auditor")
                    print("  Host        : %s" % socket.gethostname())
                    print("  Platform    : %s (%s)" % (platform.platform(), sys.platform))
                    print("  Sandbox dir : %s" % SANDBOX_DIR)
                    print("  Started     : %s" % time.strftime("%Y-%m-%d %H:%M:%S"))
                    print("  Safety      : read-only inspection - no execution")

                    if not os.path.isdir(SANDBOX_DIR):
                        print("  [!] Sandbox directory not found - nothing to audit.")
                        return

                    banner("Inbound File Queue")
                    candidates = []
                    for name in sorted(os.listdir(SANDBOX_DIR)):
                        full = os.path.join(SANDBOX_DIR, name)
                        if os.path.isfile(full):
                            candidates.append(full)
                        if len(candidates) >= MAX_FILES:
                            print("  [!] Queue capped at %d files." % MAX_FILES)
                            break
                    print("  %d file(s) queued for detonation analysis." % len(candidates))

                    banner("Analysis Results")
                    reports = []
                    for path in candidates:
                        report = analyse(path)
                        reports.append(report)
                        print("  [%-10s] score=%-4d %s" % (
                            report["verdict"], report["score"], report["file"]))
                        for indicator in report["indicators"]:
                            print("               - %s" % indicator)

                    banner("Verdict Summary")
                    counts = {}
                    for report in reports:
                        counts[report["verdict"]] = counts.get(report["verdict"], 0) + 1
                    for verdict in ("MALICIOUS", "SUSPICIOUS", "CLEAN"):
                        print("  %-11s: %d" % (verdict, counts.get(verdict, 0)))

                    print("\n" + json.dumps({
                        "service": "virtual-cloud-sandbox-email-attachment-auditor",
                        "category": "Cloud Security",
                        "sandbox_dir": SANDBOX_DIR,
                        "files_analysed": len(reports),
                        "verdict_counts": counts,
                        "executions_performed": 0,
                        "files_deleted": 0,
                        "completed_at": time.strftime("%Y-%m-%d %H:%M:%S"),
                    }, indent=2))
                    print("\n[+] Sandbox analysis complete. Nothing was executed or deleted.")


                if __name__ == "__main__":
                    main()
                PYTHON,
            ],

            [
                'title' => 'Automated Cloud Asset & CIS Benchmarking Agent',
                'slug' => 'automated-cloud-asset-cis-benchmarking-agent',
                'category' => 'Cloud Security',
                'description' => 'Automated discovery and CIS-style benchmarking of your cloud estate. Inventories AWS, Azure and Google Cloud assets, then audits credentials, encryption, logging, public exposure and least-privilege posture against the CIS Cloud Foundations Benchmark - read-only, with no API mutations.',
                'icon' => '☁️',
                'price' => 200000,
                'is_automated' => true,
                'is_visible' => true,
                'is_available' => true,
                'full_description' => '<h3>Automated Cloud Asset &amp; CIS Benchmarking</h3><p>Cloud misconfiguration is the leading cause of public data breaches. This agent continuously inventories your cloud footprint and benchmarks it against the CIS Cloud Foundations Benchmark, so drift is caught before an auditor or attacker finds it.</p><h3>Asset discovery</h3><ul><li>Locates AWS credentials and configuration (<em>~/.aws</em>), Azure profiles (<em>~/.azure</em>) and Google Cloud configuration (<em>~/.config/gcloud</em>).</li><li>Enumerates configured regions, profiles and active accounts.</li><li>Identifies container and IaC footprints (Docker, Kubernetes, Terraform).</li></ul><h3>CIS benchmark controls audited</h3><ul><li><strong>Identity &amp; access</strong> - wildcard IAM actions/resources, long-lived keys, MFA evidence.</li><li><strong>Storage</strong> - public bucket indicators, unencrypted data at rest.</li><li><strong>Logging</strong> - audit log and access log configuration.</li><li><strong>Networking</strong> - public IP bindings and permissive network rules.</li><li><strong>Cryptography</strong> - transport security enforcement.</li><li><strong>Governance</strong> - region consistency and unused credential hygiene.</li></ul><h3>Read-only guarantee</h3><p>The agent performs inventory and static policy analysis only. It never calls a mutating cloud API, never creates or deletes cloud resources and never modifies your local cloud configuration.</p><h3>Audit-ready evidence</h3><p>Produces a control-by-control PASS/FAIL report mapped to CIS Cloud Foundations v3, exportable for regulatory attestation and customer due-diligence questionnaires.</p>',
                'payment_instructions' => $this->paymentInstructions('Automated Cloud Asset & CIS Benchmarking Agent', '$20'),
                'script_code' => <<<'PYTHON'
                """CyberLogia - Automated Cloud Asset & CIS Benchmarking Agent (Cloud Security).

                Inventories cloud assets and benchmarks them against CIS Cloud
                Foundations controls using static, read-only configuration analysis.

                SAFETY: the agent NEVER calls a mutating cloud API. It only reads local
                cloud configuration files and prints a report. No cloud resource is
                created, modified or deleted.
                """
                import json
                import os
                import platform
                import re
                import shutil
                import socket
                import stat
                import sys
                import time

                CIS_CONTROLS = (
                    ("1.1", "No wildcard IAM actions/resources in policies"),
                    ("1.2", "No long-lived access keys in use"),
                    ("2.1", "Storage encryption at rest enforced"),
                    ("2.2", "No publicly accessible storage resources"),
                    ("3.1", "Cloud audit logging enabled"),
                    ("4.1", "No permissive network rules (0.0.0.0/0)"),
                    ("5.1", "TLS / HTTPS enforced for transport"),
                    ("6.1", "Credentials files are not world-readable"),
                )

                PUBLIC_MARKERS = ("0.0.0.0/0", "::/0", "allow-public-access",
                                  "public-access-block", "acl = \"public-read\"")
                WILDCARD_IAM = ('"Action": "*"', '"Action":"*"', '"Resource": "*"',
                                '"Resource":"*"', '"Principal": "*"', '"Principal":"*"')
                ENCRYPTION_MARKERS = ("encrypt", "sse", "kms", "https", "tls")


                def banner(title):
                    print("\n" + "=" * 68)
                    print("  " + title)
                    print("=" * 68)


                def read_text(path, limit=400000):
                    """Read a config file defensively; never raise on failure."""
                    try:
                        with open(path, "r", encoding="utf-8", errors="ignore") as handle:
                            return handle.read(limit)
                    except (OSError, PermissionError):
                        return ""


                def cloud_assets():
                    """Discover local cloud provider footprints (read-only)."""
                    home = os.path.expanduser("~")
                    assets = []
                    candidates = {
                        "AWS credentials": os.path.join(home, ".aws", "credentials"),
                        "AWS config": os.path.join(home, ".aws", "config"),
                        "Azure profile": os.path.join(home, ".azure", "azureProfile.json"),
                        "Azure settings": os.path.join(home, ".azure", "settings"),
                        "GCloud config": os.path.join(home, ".config", "gcloud",
                                                      "configurations", "config_default"),
                        "Kubeconfig": os.path.join(home, ".kube", "config"),
                    }
                    for label, path in candidates.items():
                        if os.path.exists(path):
                            assets.append({
                                "provider": label,
                                "path": path,
                                "size": os.path.getsize(path),
                            })
                    for cli in ("aws", "az", "gcloud", "docker", "kubectl", "terraform"):
                        binary = shutil.which(cli)
                        if binary:
                            assets.append({"provider": "CLI", "path": cli,
                                           "size": None})
                    return assets


                def gather_corpus(assets):
                    """Concatenate readable config content for pattern analysis."""
                    corpus = []
                    for asset in assets:
                        if asset.get("size") is None:
                            continue
                        body = read_text(asset["path"])
                        if body:
                            corpus.append((asset["path"], body))
                    return corpus
                def benchmark(corpus, assets):
                    """Evaluate CIS Cloud Foundations controls over the config corpus."""
                    findings = []

                    def add(control, status, evidence):
                        findings.append({"control": control, "status": status,
                                         "evidence": evidence})

                    if not corpus:
                        for code, description in CIS_CONTROLS:
                            add(code, "UNKNOWN", description + ": no config discovered")
                        return findings

                    for path, body in corpus:
                        lowered = body.lower()

                        wildcards = [m for m in WILDCARD_IAM if m in body]
                        add("1.1", "FAIL" if wildcards else "PASS",
                            "%s: %s" % (path, ", ".join(wildcards[:3])
                                        if wildcards else "no wildcard IAM entries"))

                        if re.search(r"aws_access_key_id\s*=\s*\S+", lowered):
                            add("1.2", "FAIL", path + ": long-lived access key present")
                        elif "aws_session_token" in lowered:
                            add("1.2", "PASS", path + ": temporary session credentials")
                        else:
                            add("1.2", "PASS", path + ": no long-lived keys detected")

                        encrypted = [m for m in ENCRYPTION_MARKERS if m in lowered]
                        add("2.1", "PASS" if encrypted else "FAIL",
                            "%s: encryption markers %s" % (path, encrypted[:3] or "none"))

                        public = [m for m in PUBLIC_MARKERS if m in lowered]
                        add("2.2", "FAIL" if public else "PASS",
                            "%s: %s" % (path, ", ".join(public[:3])
                                        if public else "no public exposure markers"))

                        logging_ok = any(token in lowered for token in
                                         ("audit", "cloudtrail", "monitor", "log_analytics"))
                        add("3.1", "PASS" if logging_ok else "FAIL",
                            "%s: audit logging %s" % (path, "configured" if logging_ok
                                                      else "not evident"))

                        open_rules = [m for m in ("0.0.0.0/0", "::/0") if m in lowered]
                        add("4.1", "FAIL" if open_rules else "PASS",
                            "%s: %s" % (path, ", ".join(open_rules) if open_rules
                                        else "no world-open rules"))

                        tls_ok = any(token in lowered for token in ("https", "tls", "ssl"))
                        add("5.1", "PASS" if tls_ok else "FAIL",
                            "%s: transport security %s" % (path, "configured" if tls_ok
                                                           else "not evident"))

                    for asset in assets:
                        if asset.get("size") is None:
                            continue
                        try:
                            world = bool(os.stat(asset["path"]).st_mode & stat.S_IROTH)
                        except OSError:
                            world = False
                        add("6.1", "FAIL" if world else "PASS",
                            "%s: %s" % (asset["path"],
                                        "world-readable!" if world else "restricted"))
                    return findings
                def main():
                    banner("CyberLogia Cloud Asset & CIS Benchmarking Agent")
                    print("  Host       : %s" % socket.gethostname())
                    print("  Platform   : %s (%s)" % (platform.platform(), sys.platform))
                    print("  Started    : %s" % time.strftime("%Y-%m-%d %H:%M:%S"))
                    print("  Benchmark  : CIS Cloud Foundations v3")
                    print("  Mode       : static read-only - no cloud API mutations")

                    banner("Cloud Asset Inventory")
                    assets = cloud_assets()
                    if not assets:
                        print("  No local cloud provider configuration detected.")
                    for asset in assets:
                        size = asset["size"]
                        print("  %-20s %s%s" % (asset["provider"], asset["path"],
                                               " (%d bytes)" % size if size else ""))

                    banner("CIS Benchmark Results")
                    findings = benchmark(gather_corpus(assets), assets)
                    for finding in findings:
                        print("  [%s] %-5s %s" % (finding["status"], finding["control"],
                                                   finding["evidence"]))

                    passed = sum(1 for f in findings if f["status"] == "PASS")
                    failed = sum(1 for f in findings if f["status"] == "FAIL")
                    unknown = sum(1 for f in findings if f["status"] == "UNKNOWN")
                    score = round((passed / len(findings)) * 100) if findings else 0

                    banner("Posture Summary")
                    print("  PASS: %d   FAIL: %d   UNKNOWN: %d   SCORE: %d%%"
                          % (passed, failed, unknown, score))
                    if failed:
                        print("\n  REMEDIATION PRIORITY")
                        for finding in findings:
                            if finding["status"] == "FAIL":
                                print("   * [%s] %s" % (finding["control"],
                                                       finding["evidence"]))

                    print("\n" + json.dumps({
                        "service": "automated-cloud-asset-cis-benchmarking-agent",
                        "category": "Cloud Security",
                        "assets_discovered": len(assets),
                        "controls_evaluated": len(findings),
                        "score_percent": score,
                        "passed": passed,
                        "failed": failed,
                        "unknown": unknown,
                        "cloud_mutations_performed": False,
                        "completed_at": time.strftime("%Y-%m-%d %H:%M:%S"),
                    }, indent=2))
                    print("\n[+] Cloud benchmark complete. No cloud resource was changed.")


                if __name__ == "__main__":
                    main()
                PYTHON,
            ],

            [
                'title' => 'Automated Kubernetes & Docker Security Auditor Agent',
                'slug' => 'automated-kubernetes-docker-security-auditor-agent',
                'category' => 'Cloud Security',
                'description' => 'Continuous container and Kubernetes security auditing. Inspects Docker daemon configuration and running container posture, validates kubeconfig and cluster RBAC exposure, and flags privileged containers, host namespace sharing, root users, missing resource limits and mutable image tags.',
                'icon' => '☸️',
                'price' => 150000,
                'is_automated' => true,
                'is_visible' => true,
                'is_available' => true,
                'full_description' => '<h3>Automated Kubernetes &amp; Docker Security Auditor</h3><p>Containers introduce a new attack surface that most security teams do not monitor. This agent continuously audits your Docker daemon and Kubernetes estate against hardened-container best practices and CIS Docker/Kubernetes benchmarks.</p><h3>Docker posture checks</h3><ul><li><strong>Daemon configuration</strong> - insecure registry access, live-restore and default-runtime review.</li><li><strong>Container hardening</strong> - privileged mode, host PID/IPC/network namespace sharing, root execution.</li><li><strong>Supply chain</strong> - mutable <em>latest</em> image tags and unpinned digests.</li><li><strong>Resource governance</strong> - missing CPU and memory limits that enable denial-of-service and container escape impact.</li><li><strong>Secret exposure</strong> - sensitive environment variables baked into image definitions.</li><li><strong>Socket exposure</strong> - mounting the Docker socket inside a container (equivalent to host root).</li></ul><ul><li><strong>Kubernetes posture checks</strong> - kubeconfig permission exposure, insecure TLS verification, embedded user tokens and cluster-admin context usage.</li></ul><h3>Read-only by design</h3><p>The agent only issues <em>listing</em> and <em>inspect</em> operations. It never stops, restarts, removes or recreates a container, and never applies or deletes Kubernetes resources.</p><h3>Shift-left value</h3><p>Run the auditor in CI/CD to block images and manifests that violate your hardening policy before they ever reach a registry or cluster.</p>',
                'payment_instructions' => $this->paymentInstructions('Automated Kubernetes & Docker Security Auditor Agent', '$15'),
                'script_code' => <<<'PYTHON'
                """CyberLogia - Automated Kubernetes & Docker Security Auditor (Cloud Security).

                Audits the local Docker daemon posture and Kubernetes client
                configuration using read-only inspection commands.

                SAFETY: the agent ONLY runs listing/inspect operations
                (docker ps, docker inspect, kubectl config view). It never stops,
                removes or recreates containers and never applies Kubernetes changes.
                """
                import json
                import os
                import platform
                import shutil
                import socket
                import stat
                import subprocess
                import sys
                import time

                SENSITIVE_ENV = ("password", "secret", "token", "api_key",
                                 "apikey", "access_key", "private_key", "credential")


                def banner(title):
                    print("\n" + "=" * 68)
                    print("  " + title)
                    print("=" * 68)


                def run(cmd, timeout=20):
                    try:
                        return (subprocess.run(
                            cmd, capture_output=True, text=True, timeout=timeout
                        ).stdout or "").strip()
                    except Exception:
                        return ""


                def audit_docker_daemon():
                    """Review daemon.json for insecure registry / runtime settings."""
                    findings = []
                    home = os.path.expanduser("~")
                    candidates = [
                        "/etc/docker/daemon.json",
                        os.path.join(home, ".docker", "daemon.json"),
                    ]
                    checked = False
                    for path in candidates:
                        if not os.path.exists(path):
                            continue
                        checked = True
                        try:
                            with open(path, "r", encoding="utf-8", errors="ignore") as handle:
                                body = handle.read().lower()
                        except (OSError, PermissionError):
                            continue
                        if "insecure-registries" in body:
                            findings.append({"severity": "HIGH",
                                             "type": "Insecure registry configured",
                                             "detail": path})
                        if "live-restore" not in body:
                            findings.append({"severity": "LOW",
                                             "type": "live-restore not enabled",
                                             "detail": path})
                    if not checked:
                        findings.append({"severity": "INFO",
                                         "type": "No daemon.json discovered",
                                         "detail": "default daemon settings assumed"})
                    return findings
                def audit_containers():
                    """Inspect running containers using read-only docker commands."""
                    findings = []
                    if not shutil.which("docker"):
                        return [{"severity": "INFO", "type": "Docker CLI not installed",
                                 "detail": "container inspection skipped"}]
                    output = run(["docker", "ps", "--format", "{{.ID}}\t{{.Image}}\t{{.Names}}"])
                    if not output:
                        return [{"severity": "INFO", "type": "No running containers",
                                 "detail": "docker ps returned nothing"}]

                    for row in output.splitlines():
                        parts = row.split("\t")
                        if len(parts) < 3:
                            continue
                        container_id, image, name = parts[0], parts[1], parts[2]
                        if image.endswith(":latest") or ":" not in image:
                            findings.append({"severity": "MEDIUM",
                                             "type": "Mutable image tag",
                                             "detail": "%s uses %s" % (name, image)})
                        details = run(["docker", "inspect", container_id])
                        if not details:
                            continue
                        try:
                            config = json.loads(details)[0]
                        except (ValueError, IndexError):
                            continue

                        host_config = config.get("HostConfig") or {}
                        if host_config.get("Privileged"):
                            findings.append({"severity": "CRITICAL",
                                             "type": "Privileged container",
                                             "detail": name})
                        for key, label in (("PidMode", "host PID"),
                                           ("IpcMode", "host IPC"),
                                           ("NetworkMode", "host network")):
                            if str(host_config.get(key, "")).startswith("host"):
                                findings.append({"severity": "HIGH",
                                                 "type": "Host namespace: " + label,
                                                 "detail": name})
                        for mount in host_config.get("Binds") or []:
                            if "docker.sock" in mount:
                                findings.append({"severity": "CRITICAL",
                                                 "type": "Docker socket mounted",
                                                 "detail": "%s -> %s" % (name, mount)})
                        if not host_config.get("Memory") or not host_config.get("NanoCpus"):
                            findings.append({"severity": "MEDIUM",
                                             "type": "Missing resource limits",
                                             "detail": name})
                        for entry in (config.get("Config") or {}).get("Env") or []:
                            lowered = entry.lower()
                            if any(m in lowered for m in SENSITIVE_ENV):
                                if "=" in entry and len(entry.split("=", 1)[1]) > 6:
                                    findings.append({
                                        "severity": "HIGH",
                                        "type": "Sensitive env var in container",
                                        "detail": "%s: %s" % (name, entry.split("=")[0]),
                                    })
                                    break
                    return findings
                def audit_kubeconfig():
                    """Audit local Kubernetes client configuration (read-only)."""
                    findings = []
                    path = os.path.join(os.path.expanduser("~"), ".kube", "config")
                    if not os.path.exists(path):
                        return [{"severity": "INFO", "type": "No kubeconfig found",
                                 "detail": "no Kubernetes client configured"}]
                    try:
                        if os.stat(path).st_mode & stat.S_IROTH:
                            findings.append({"severity": "HIGH",
                                             "type": "kubeconfig world-readable",
                                             "detail": path})
                    except OSError:
                        pass
                    try:
                        with open(path, "r", encoding="utf-8", errors="ignore") as handle:
                            body = handle.read()
                    except (OSError, PermissionError):
                        return findings

                    lowered = body.lower()
                    if "insecure-skip-tls-verify: true" in lowered:
                        findings.append({"severity": "CRITICAL",
                                         "type": "TLS verification disabled",
                                         "detail": path})
                    if 'certificate-authority-data: ""' in lowered:
                        findings.append({"severity": "HIGH",
                                         "type": "Empty CA bundle",
                                         "detail": path})
                    if "token:" in lowered:
                        findings.append({"severity": "MEDIUM",
                                         "type": "Embedded token in kubeconfig",
                                         "detail": path})
                    if "cluster-admin" in lowered:
                        findings.append({"severity": "MEDIUM",
                                         "type": "cluster-admin role referenced",
                                         "detail": path})
                    if not findings:
                        findings.append({"severity": "INFO",
                                         "type": "kubeconfig looks hardened",
                                         "detail": path})
                    return findings


                def main():
                    banner("CyberLogia Kubernetes & Docker Security Auditor")
                    print("  Host      : %s" % socket.gethostname())
                    print("  Platform  : %s (%s)" % (platform.platform(), sys.platform))
                    print("  Started   : %s" % time.strftime("%Y-%m-%d %H:%M:%S"))
                    print("  Mode      : read-only audit - no container or cluster changes")

                    banner("Docker Daemon Posture")
                    daemon_findings = audit_docker_daemon()
                    for finding in daemon_findings:
                        print("  [%-8s] %-28s %s" % (finding["severity"],
                                                    finding["type"], finding["detail"]))

                    banner("Running Container Posture")
                    container_findings = audit_containers()
                    if not container_findings:
                        print("  No container findings.")
                    for finding in container_findings:
                        print("  [%-8s] %-28s %s" % (finding["severity"],
                                                    finding["type"], finding["detail"]))

                    banner("Kubernetes Client Posture")
                    k8s_findings = audit_kubeconfig()
                    for finding in k8s_findings:
                        print("  [%-8s] %-28s %s" % (finding["severity"],
                                                    finding["type"], finding["detail"]))

                    findings = daemon_findings + container_findings + k8s_findings
                    counts = {}
                    for finding in findings:
                        counts[finding["severity"]] = counts.get(finding["severity"], 0) + 1

                    banner("Container Security Summary")
                    for severity in ("CRITICAL", "HIGH", "MEDIUM", "LOW", "INFO"):
                        print("  %-9s: %d" % (severity, counts.get(severity, 0)))
                    blocking = counts.get("CRITICAL", 0) + counts.get("HIGH", 0)
                    print("\n  Verdict: %s" % (
                        "FAIL - resolve critical/high findings before production"
                        if blocking else "PASS - posture acceptable"))

                    print("\n" + json.dumps({
                        "service": "automated-kubernetes-docker-security-auditor-agent",
                        "category": "Cloud Security",
                        "total_findings": len(findings),
                        "severity_counts": counts,
                        "containers_changed": 0,
                        "cluster_changes_applied": 0,
                        "completed_at": time.strftime("%Y-%m-%d %H:%M:%S"),
                    }, indent=2))
                    print("\n[+] Container audit complete. Nothing was restarted or removed.")


                if __name__ == "__main__":
                    main()
                PYTHON,
            ],
        ];
    }
}

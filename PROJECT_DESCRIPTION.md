# CyberLogia — Complete Project Description

> **Purpose of this document:** A single, self-contained description of the entire project, detailed enough for a human to read or for an AI agent to understand the whole codebase **without needing to scan the source files**.
>
> **Repository:** `https://github.com/KaramSALEH1/CyberWithKaram.git` · **Branch:** `main`
>
> ⚠️ **Branding note:** the platform was formerly named *CyberWithKaram*. All user-facing
> branding (views, layouts, page titles, seeders, generated agents) now uses **CyberLogia**.
> The repository/working-tree paths still carry the old name.

---

## 1. What This Project Is

**CyberLogia** is a **cybersecurity-services SaaS marketplace** built with **Laravel 12 (PHP 8.2+)**. It sells three product lines from one website:

1. **Automated Security Agents Marketplace** — 10 production-grade, fully automated agent services across three categories: **Blue Team** (EDR & threat hunting, CIS hardening, log collector/SIEM forwarder, FIM), **Red Team** (ransomware/breach simulation, internal vulnerability scanner, local app misconfiguration audit) and **Cloud Security** (cloud sandbox & email attachment auditor, cloud asset CIS benchmarking, Kubernetes & Docker auditor). Every service ships as a self-installing Python agent.
2. **"Academy"** — a course platform (Courses → Modules → Lessons) with video content (YouTube or self-hosted uploads) and per-course/module/lesson paid access.
3. **Remote Agent / Command Center** — a fleet-management system for Python "agents" running on client machines. Admins queue signed commands; agents poll for them and return results. The platform also pushes executable Python `script_code` to licensed agents.

**Monetization model:** **Manual payment verification** — the customer submits a bank/wallet transfer receipt (account name, transaction amount, reference ID) in a checkout form; an admin reviews it in the dashboard and approves/rejects it. On approval the platform issues a **license key** (`CWK-` + 16 random uppercase chars) for services, or creates an **Entitlement** for academy content. Access lasts **30 days** (`expires_at`). Currency: **SYP (Syrian Pounds)**.

**Notifications:** **Telegram bot** alerts (HTML messages) for: new payment receipts uploaded, payment approved (with license key), and agents going offline. Admin actions are also written to an `action_logs` audit table.

---

## 2. The CyberLogia Service Catalog (10 automated agents)

`database/seeders/DatabaseSeeder.php` clears `services` (FK-safe via
`Schema::disableForeignKeyConstraints()`) and seeds exactly **10** automated agent
services. Each row carries a short `description`, an HTML `full_description`
(sanitized on write — see §13), `payment_instructions` (SYP + USD), an `icon`, a
monthly `price`, and a working, benign `script_code` payload.

| # | Service (`slug`) | Category | Price (SYP) | ≈USD/mo | Icon | Payload behaviour |
|---|---|---|---|---|---|---|
| 1 | Automated EDR & Threat Hunting Agent (`automated-edr-threat-hunting-agent`) | Blue Team | 80,000 | $8 | 🛡️ | Process inventory, listening sockets, persistence entries, LOLBin heuristic hunting |
| 2 | System Hardening & CIS Compliance Check (`system-hardening-cis-compliance-check`) | Blue Team | 50,000 | $5 | 🔐 | PASS/FAIL scorecard: firewall, guest account, SMB signing, RDP/SSH, audit logging, password policy |
| 3 | Automated Log Collector & SIEM Forwarder Agent (`automated-log-collector-siem-forwarder-agent`) | Blue Team | 60,000 | $6 | 📊 | Discovers log sources, tails **new** bytes via an offset cursor, classifies severity, forwards to a SIEM |
| 4 | File Integrity Monitoring (FIM) Agent (`file-integrity-monitoring-fim-agent`) | Blue Team | 40,000 | $4 | 🔗 | SHA-256 baseline of critical paths; reports ADDED / MODIFIED / DELETED |
| 5 | Continuous Ransomware & Breach Simulation Agent (`continuous-ransomware-breach-simulation-agent`) | Red Team | 100,000 | $10 | 🚨 | **Non-destructive.** Backup/snapshot validation, exposure scoring, detection readiness. Never encrypts client data; sandbox is temp-only and self-deleted |
| 6 | Automated Internal Network Vulnerability Scanner (`automated-internal-network-vulnerability-scanner`) | Red Team | 250,000 | $25 | 📡 | TCP-**connect-only** host discovery + service fingerprinting. Refuses non-RFC1918 ranges; no exploitation |
| 7 | Local Application Vulnerability & Misconfig Agent (`local-application-vulnerability-misconfig-agent`) | Red Team | 70,000 | $7 | 🎯 | Read-only **static** analysis: hardcoded secrets, debug flags, weak crypto, CORS, world-writable files |
| 8 | Virtual Cloud Sandbox & Email Attachment Auditor (`virtual-cloud-sandbox-email-attachment-auditor`) | Cloud Security | 350,000 | $35 | 📨 | Magic-byte type verification, SHA-256, archive listing (**never extracted**), malware heuristic scoring |
| 9 | Automated Cloud Asset & CIS Benchmarking Agent (`automated-cloud-asset-cis-benchmarking-agent`) | Cloud Security | 200,000 | $20 | ☁️ | Inventories AWS/Azure/GCP config + CLIs; benchmarks 8 CIS Cloud Foundations controls. No mutating API calls |
| 10 | Automated Kubernetes & Docker Security Auditor Agent (`automated-kubernetes-docker-security-auditor-agent`) | Cloud Security | 150,000 | $15 | ☸️ | Read-only `docker ps`/`docker inspect` + kubeconfig audit: privileged, host namespaces, root, missing limits, TLS skip |

**Safety invariant:** every seeded payload is strictly diagnostic/read-only. The
only file writes are the FIM agent's own baseline (`~/.cyberlogia/fim-baseline.json`)
and the ransomware simulator's `tempfile.mkdtemp()` sandbox, which is removed in a
`finally` block. All 10 were verified to execute cleanly (exit code 0) on Windows.

Pricing is derived for display via `Service::usdPrice()` / `Service::priceLabel()`,
which divide `price` by `config('cyberlogia.syp_per_usd')` (default `10000`).

---

## 3. Technology Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 (`laravel/framework ^12.0`), PHP `^8.2` |
| Auth (web) | Laravel Breeze (`^2.4`, dev dep) — Blade + Alpine.js session auth, email verification |
| Auth (API) | Laravel Sanctum (`^4.3`) — personal access tokens for Python agents |
| Database | **MySQL / MariaDB** (`cyber_with_karam` @ `127.0.0.1:3306`); sessions/cache/queues on `database` driver |
| Frontend | Blade, **Tailwind CSS 3** (`@tailwindcss/forms`, custom `karam-green: #008751`, Figtree font), **Alpine.js 3**, **Vite 7** (`laravel-vite-plugin`), Axios |
| Queue | `database` queue driver; workers via `php artisan queue:listen` |
| Tests | PHPUnit `^11.5.50` (MySQL test DB `cyber_with_karam_test`, `QUEUE_CONNECTION=sync`) |
| Python agent | Generated `agent_bootstrapper.py` (needs `requests`; auto-installed). Seeded service payloads use **only the standard library** |
| Other | Laravel Pint, Pail, Sail, Tinker, Faker, Mockery, Collision, `concurrently` |
| Notifications | Telegram Bot API via `Http` facade (`config/telegram.php` ← `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID`). Failures are caught and logged — they never break a core flow |
| Marketplace config | `config/cyberlogia.php` — `brand`, `syp_per_usd` (SYP→USD nominal rate), `agent_heartbeat_interval` |

**Composer scripts:**
- `composer setup` — install, copy `.env`, `key:generate`, `migrate --force`, `npm install`, `npm run build`
- `composer dev` — concurrently: `php artisan serve` + `queue:listen` + `pail` + `npm run dev`
- `composer test` — `config:clear` + `php artisan test`

**Environment (`.env`):** `APP_URL=http://localhost`, `DB_CONNECTION=mysql`, `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database`, `CACHE_STORE=database`, `MAIL_MAILER=log`, Telegram vars configured.

**Health endpoint:** `/up` (registered in `bootstrap/app.php`).

---

## 4. Directory Structure (project-owned code)

```
CyberLogia/                            # repo dir still named CyberWithKaram
├── agent.py                          # Standalone Python agent (legacy/demo client)
├── artisan
├── bootstrap/app.php                 # Routing, middleware aliases, CSRF exceptions
├── composer.json / composer.lock
├── package.json / vite.config.js / tailwind.config.js / postcss.config.js
├── phpunit.xml
├── stubs/agent_bootstrapper.py       # Template rendered into every agent download
├── .cursorrules                      # AI-coding rules for this project
├── .env / .env.example
├── config/
│   ├── app, auth, cache, database, filesystems, logging, mail,
│   │   queue, sanctum, services (standard Laravel)
│   └── telegram.php                # bot_token + chat_id from env
├── app/
│   ├── Console/Commands/CheckOfflineAgents.php
│   ├── Http/Controllers/
│   │   ├── AcademyController.php           # Admin CRUD courses/modules/lessons
│   │   ├── AdminController.php             # Admin dashboard stats
│   │   ├── CourseController.php            # Public course/lesson pages
│   │   ├── PageController.php
│   │   ├── PaymentController.php           # Checkout + receipt submission + mock pay
│   │   ├── ProfileController.php
│   │   ├── UserToolController.php          # "My Tools": token + agent download
│   │   ├── Admin/{CommandCenter,Course,Payment,Service}Controller.php
│   │   ├── Api/{AgentApiController, AgentController}.php
│   │   └── Auth/*                          # Breeze auth controllers (9 files)
│   ├── Http/Middleware/{AuthenticateAgent, EnsureAdmin}.php
│   ├── Http/Requests/                      # FormRequests (see §8)
│   ├── Jobs/{DispatchAgentCommandJob, ProcessAgentResultJob}.php
│   ├── Models/                             # 14 models (see §5)
│   ├── Policies/{AgentCommandPolicy, CoursePolicy, LessonPolicy, ModulePolicy}.php
│   ├── Providers/AppServiceProvider.php    # empty stub
│   ├── Services/
│   │   ├── Academy/EntitlementService.php
│   │   ├── ActionLog/ActionLogService.php
│   │   ├── Agent/AgentProtocolService.php
│   │   ├── CommandCenter/{CommandDispatchService, CommandSigningService}.php
│   │   ├── Payment/PaymentVerificationService.php
│   │   ├── Service/ServiceManagementService.php
│   │   └── Telegram/TelegramService.php
│   ├── Support/SecureVideoUpload.php
│   └── View/Components/{AppLayout, GuestLayout}.php
├── database/
│   ├── factories/UserFactory.php
│   ├── migrations/   # 38 migrations (see §6)
│   └── seeders/DatabaseSeeder.php          # 10 demo security services
├── public/           # standard Laravel public dir (index.php, favicon, robots)
├── resources/
│   ├── css/app.css, js/app.js, js/bootstrap.js
│   └── views/        # 57 Blade files (see §9)
├── routes/{web.php, api.php, auth.php, console.php}
├── storage/          # logs, cache, sessions, views, videos (public disk)
├── tests/            # 12 test files (see §10)
└── vendor/           # Composer dependencies (ignore)
```

> ⚠️ `README.md` is still the **default Laravel README** — this file replaces it as real documentation.
> ⚠️ The on-disk repository/folder name is still `CyberWithKaram`; only the brand is **CyberLogia**.


---

## 5. Routes

### 5.1 Web (`routes/web.php`)

**Public:**
| Method | URI | Name | Notes |
|---|---|---|---|
| GET | `/` | `home` | Landing page; shows visible services (guards with `Schema::hasTable`) |
| GET | `/about` | `about` | Static |
| GET | `/contact` | `ContactController@show` | Contact page (support channels + enquiry form) |
| POST | `/contact` | `ContactController@store` | Validates the enquiry and forwards it to the Telegram operations channel |
| GET | `/services` | `services` | Lists `is_visible` services |
| GET | `/services/{service:slug}` | `service.show` | Details; computes `hasApprovedAccess` + `userLicenseKey` for logged-in user |
| GET | `/courses` | `courses` | Active courses |
| GET | `/courses/{course:slug}` | `courses.show` | Curriculum; `hasCourseAccess` flag |
| GET | `/courses/{course:slug}/lessons/{lesson:slug}` | `lessons.show` | Lesson player w/ prev/next; free-lesson or entitlement gate |

**Authenticated + verified (`auth`,`verified`):**
| Method | URI | Name |
|---|---|---|
| GET | `/services/{slug}/pay` | `services.pay` |
| GET | `/courses/{slug}/checkout` | `courses.checkout` |
| GET | `/academy/modules/{id}/checkout` | `modules.checkout` |
| GET | `/academy/lessons/{slug}/checkout` | `lessons.checkout` |
| POST | `/payment/submit` | `payments.submit` (receipt upload) |
| GET | `/payment/mock-success/{type}/{slug}` | `payment.mock-global-success` (dev shortcut that instantly approves) |

**Academy JSON gates** (prefix `/academy`, `auth`+`verified`): `academy.course.show`, `academy.module.show`, `academy.lesson.show` — return JSON or **403** based on `EntitlementService`.

**Admin** (prefix `/admin`, middleware `auth`,`verified`,`admin`):
- `GET /admin/dashboard` → `admin.dashboard` (uses `AdminController@index` → `dashboard` view)
- **Command Center:** `GET /admin/command-center` (`admin.command-center.index`), `POST .../commands` (store), `POST .../commands/{command}/cancel` (cancel)
- **Payments:** `GET /admin/payments` (list, filter `?status=`), `GET /admin/payments/{payment}`, `POST .../approve`, `POST .../reject`
- **Subscriptions report:** `GET /admin/subscriptions` → `admin.subscriptions.index` (`Admin\SubscriptionController@index`). Analytics dashboard for subscribers, licence keys and lifecycle. Supports `?status=` (`all|active|expiring|expired|pending|rejected`) and `?q=` (searches user name/email, licence key and service title). Renders 8 stat cards (total, active, expiring soon, expired, pending, licences issued, active customers, active MRR in SYP), a per-category catalogue breakdown, status badges and a paginated table. Linked from the admin sidebar.
- **Academy** (prefix `/admin/academy`): course/module/lesson `store`, `{id}/edit`, `PUT`, `DELETE`; `GET /admin/academy/courses/{course}` → `admin.course.show`
- **Services:** full `Route::resource` (`admin.services.*`)

**Authenticated only (`auth`):**
- `GET/PATCH/DELETE /profile` (`profile.*`)
- `GET /my-tools` → `my-tools.index` (**deletes old `agent-api` Sanctum tokens and issues a fresh one on every visit**)
- `GET /my-tools/download-agent/{service_id}/{license_key}` → streams a generated `agent_bootstrapper.py`

`routes/auth.php` = standard Breeze auth routes (register, login, password reset, email verification, confirm password, logout).

### 5.2 API (`routes/api.php`) — CSRF-exempt (`api/*` excluded in `bootstrap/app.php`)

Rate limiters defined in `AppServiceProvider::boot()`:

| Limiter | Budget | Purpose |
|---|---|---|
| `api` | 60/min per user-or-IP | General API budget |
| `agent-auth` | 30/min **and** 500/hour, keyed by `X-Agent-Key` hash | v1 register/poll/result — credential-stuffing defence |
| `agent-script` | 10/min per authenticated user | `/api/fetch-script` — returns executable code and accepts a brute-forceable licence key |

**Licensed agent API**:
| Method | URI | Middleware | Controller | Purpose |
|---|---|---|---|---|
| GET | `/api/fetch-script?service_id&license_key` | `auth:sanctum` + `throttle:agent-script` | `AgentApiController@fetchScript` | Returns `service.script_code` if licence valid & non-expired |
| POST | `/api/heartbeat` | `auth:sanctum` + `throttle:api` | `AgentApiController@heartbeat` | Python agent heartbeat (requires approved licence) |
| POST | `/api/agent/token` | `auth:sanctum` + `throttle:api` | `AgentApiController@createToken` | Creates a `python-agent` Sanctum token |

Both `/fetch-script` and the agent download respond with
`Cache-Control: no-store, private`, `Pragma: no-cache` and
`Referrer-Policy: no-referrer`, because the licence key travels in the URL
(→ web-server access logs) and the download body embeds a live Sanctum token.

**v1 Agent protocol** (`throttle:agent-auth`; legacy alias at `/api/v1/agent/*`):
| Method | URI | Middleware | Purpose |
|---|---|---|---|
| POST | `/api/v1/agents/register` | `auth:sanctum` | Register agent → returns one-time `api_token` |
| POST | `/api/v1/agents/heartbeat` | `agent.auth` | Update agent presence + heartbeat record |
| POST | `/api/v1/agents/poll` | `agent.auth` | Next queued, unexpired command |
| POST | `/api/v1/agents/result` | `agent.auth` | Submit command result (replay-protected) |

`agent.auth` (`app/Http/Middleware/AuthenticateAgent.php`) requires **both** an
`X-Agent-Key` header and a bearer token, compared with
`hash_equals($agent->api_token_hash, hash('sha256', $token))`.

### 5.3 Console (`routes/console.php`)
- `inspire` command.
- **Schedule:** `agents:check-offline` **every 5 minutes**.

### 5.4 Middleware aliases (`bootstrap/app.php`)
- `agent.auth` → `App\Http\Middleware\AuthenticateAgent`
- `admin` → `App\Http\Middleware\EnsureAdmin`

---

## 6. Data Model (14 Eloquent Models)

| Model | Key fields / notes |
|---|---|
| **User** | `name, email, password, is_admin(bool)`; traits `HasApiTokens, HasFactory, Notifiable`; relations: `agents, purchases, entitlements, payments, agentStatuses, actionLogs` |
| **Service** | `title, slug(unique, route key), category, description, full_description (HTML allow-list sanitized via mutator), icon, logo_url, price(decimal), is_automated, is_visible, is_available, payment_instructions, script_code (longText)`; `requiresPayment()`; relations `payments`, `agentStatuses` |
| **Payment** | `user_id, service_id (nullable), product_id (nullable), product_type ∈ {service,course,module,lesson}, amount, account_name_number, transaction_amount, transaction_id_reference, notes, status ∈ {pending,approved,rejected}, license_key, approved_at, expires_at`; `productTitle()` resolves product by type; `isApproved()` |
| **Purchase** | legacy purchase record (referenced by Entitlement) |
| **Entitlement** | `user_id, purchase_id, entitlement_type, entitlement_id, is_active, starts_at, ends_at` — grants academy access |
| **Course** | `title, slug, description, level, price, is_active, requires_purchase`; `modules()` (ordered by order_no), `lessons()` (hasManyThrough Module) |
| **Module** | `course_id, title, order_no, price, requires_purchase`; `course()`, `lessons()` |
| **Lesson** | `module_id, title, slug, content, video_url, video_path, video_type ∈ {youtube,local}, order_no, price, is_free, requires_purchase`; `module()` |
| **Agent** (v1 protocol) | `user_id, agent_key(unique), api_token_hash(sha256, unique), device_name, ip_address, os_type, agent_version, host_fingerprint, status, last_seen, token_last_rotated_at, last_nonce, metadata(json), registered_at`; relations `user, commands, heartbeats` |
| **AgentCommand** | `command_uuid, agent_id, requested_by, approved_by, command_key, payload(json), signature_hash (HMAC-SHA256), nonce(uuid), expires_at, status ∈ {queued,sent,succeeded,failed,cancelled,expired}, queued_at, sent_at, started_at, finished_at, cancelled_at, cancel_reason`; `agent, requester, approver, result` |
| **AgentCommandResult** | `agent_command_id (unique), result_status, exit_code, duration_ms, stdout, stderr, result_hash (sha256), artifacts(json), received_at` |
| **AgentHeartbeat** | per-heartbeat log: `agent_id, status, ip_address, os_type, agent_version, host_fingerprint, metadata, seen_at` |
| **AgentStatus** (simple presence) | `service_id, user_id, last_heartbeat, status ∈ {online,offline}, ip_address`; one row per (user, service); `isOnline()` |
| **ActionLog** | audit trail: `user_id, action_description, logged_at` |

**Relations summary:** User 1—* Agent, Payment, Entitlement, AgentStatus, ActionLog · Service 1—* Payment, AgentStatus · Course 1—* Module 1—* Lesson · Agent 1—* AgentCommand 1—1 AgentCommandResult, 1—* AgentHeartbeat.

---

## 7. Database Migrations (38 files)

**Framework defaults:** `users` (+password reset tokens), `cache`, `jobs` (queues).

**Domain migrations (chronological):**
1. `2026_03_30` — create `services`.
2. `2026_03_31` — update `services` add details · create `lessons` · create `courses` · create `modules`.
3. `2026_04_06` — create `agents` · `agent_commands` · `agent_command_results` · `agent_heartbeats` · `purchases` · `entitlements` · add command security fields to `agents` (api_token_hash, token_last_rotated_at, last_nonce, agent_version, host_fingerprint, metadata, registered_at) · add `users.is_admin` · add access-control fields to academy tables (prices, requires_purchase, is_free) · add `logo_url` to services.
4. `2026_05_15` — add SaaS fields to `services` (price, is_available, payment_instructions, script_code→longText) · create `agent_statuses` · create `payments` · create `action_logs` · create `personal_access_tokens`.
5. `2026_05_16` — payments: text details, `expires_at`, product fields, make `service_id` nullable · lessons: `module_id`, `is_free` + **several schema-fix migrations** (`fix_lessons_table_columns`, `align_lessons_table_schema`, `final_lessons_table_cleanup`, `force_clean_lessons_schema`) ⚠️ sign of iterative schema churn on `lessons` · `price` on courses & academy tables.


---

## 8. Core Business Logic (Services)

### 8.1 PaymentVerificationService (`app/Services/Payment/`)
- `approve(Payment, User $admin)`:
  - sets `status=approved`, `approved_at=now()`, `expires_at=now()+30 days`.
  - for `product_type === 'service'` → generates license key `CWK-` + 16 uppercase random chars (uniqueness loop against `payments`).
  - for `course|module|lesson` → `Entitlement::updateOrCreate` (active, +30 days window).
  - writes `ActionLog` + sends **Telegram** approval message (user, product, amount in **SYP**, license, approving admin, link to admin payment page).
- `reject(Payment, User)` → status `rejected` + ActionLog (no Telegram).
- `getApprovedPayment(userId, serviceId, ?licenseKey)` / `userHasApprovedAccess(...)` → latest approved payment (optionally matching license).
- **Note:** approval itself does not check expiry — expiry is enforced at script-fetch time.

### 8.2 EntitlementService (`app/Services/Academy/`)
Cascading access checks — `userHasCourseAccess`, `userHasModuleAccess`, `userHasLessonAccess`:
- Admin always has access.
- Order: approved non-expired Payment → active non-expired Entitlement → inherited from parent (lesson inherits module, module inherits course) → free if `!requires_purchase && price <= 0`.
- `userHasLessonAccess` additionally returns true when `lesson->is_free`.

### 8.3 AgentProtocolService (`app/Services/Agent/`) — v1 protocol
- `register(payload)` → creates Agent, stores **sha256 hash** of a 64-char random plain token, returns `[agent, plainToken]` (plain token shown once).
- `heartbeat(agent, payload)` → updates agent (status/ip/os/version/fingerprint/last_seen, merges metadata) + creates an `AgentHeartbeat` row.
- `nextCommand(agent)` → first `queued`, unexpired command ordered by id → marks `sent` + `sent_at`.
- `storeResult(agent, payload)` → finds command by `command_uuid`; **replay protection**: aborts 409 if `nonce === agent->last_nonce`; `updateOrCreate` result with sha256 `result_hash` of stdout+stderr; stores `last_nonce`; dispatches `ProcessAgentResultJob`.

### 8.4 CommandCenter services
- **CommandSigningService:** `HMAC-SHA256(json{command_key,payload,nonce,expires_at}, APP_KEY)`.
- **CommandDispatchService:** `dispatch(agent, requester, commandKey, payload, ttl=300s)` → uuid command, signed, status `queued`, queues `DispatchAgentCommandJob`; `cancel(command, reason)` → no-op if already finished, else `cancelled` + timestamps + reason.

### 8.5 TelegramService
- `isConfigured()` requires both `TELEGRAM_BOT_TOKEN` **and** a valid numeric `TELEGRAM_CHAT_ID`.
- `targetChatId()` resolves the destination and returns `null` when unset or non-numeric — **there is deliberately no hardcoded or legacy fallback**, so a missing/invalid value disables notifications rather than delivering them to an unintended chat.
- `sendMessage()` posts HTML to `https://api.telegram.org/bot{token}/sendMessage` with a configurable timeout (`TELEGRAM_TIMEOUT`, default 10s). `ConnectionException` is caught and logged so notification outages never break payment approval or agent registration.
- Helpers: `notifyNewPaymentReceipt(Payment)`, `notifyAgentOffline(AgentStatus)`.

**Configuration (`config/telegram.php`)**

| Key | Env | Notes |
|---|---|---|
| `bot_token` | `TELEGRAM_BOT_TOKEN` | From @BotFather. No fallback. |
| `chat_id` | `TELEGRAM_CHAT_ID` | **Numeric id only** of the operations chat (`@kachat3`). Groups start with `-100`. |
| `timeout` | `TELEGRAM_TIMEOUT` | Seconds, default `10`. |
| `chat_label` | `TELEGRAM_CHAT_LABEL` | Human-readable label, default `@kachat3`. |

Telegram cannot deliver to an `@username` directly — the numeric id must be resolved.
Use the built-in diagnostic:

```bash
php artisan telegram:test              # verify config + send a test message
php artisan telegram:test -- --resolve # list chats the bot can see, to find the id
```

### 8.6 ActionLogService — `ActionLog::create(user_id, description, logged_at)`.

### 8.7 ServiceManagementService — normalizes create/update payloads for `Service` (slug auto-generated from title, default icon 🛡️, boolean casts).

### 8.8 SecureVideoUpload (`app/Support/`)
Stores lesson videos safely: rejects path-traversal filenames; allows only `mp4/mov/avi/wmv` extension **and** matching MIME; filename = `sha256(uniqid+name).ext`; saved to `storage/app/public/videos` (public disk).


---

## 9. HTTP Layer Details

### Middleware
- **AuthenticateAgent** (`agent.auth`): requires headers `X-Agent-Key` + `Authorization: Bearer <token>`; looks up Agent by key; constant-time `hash_equals(api_token_hash, sha256(token))`; attaches `agent` to request attributes; 401 otherwise.
- **EnsureAdmin** (`admin`): 403 unless `$user->is_admin`.

### Form Requests (`app/Http/Requests/`)
- `Api\AgentRegisterRequest` — authorize: token user must equal submitted `user_id`; validates `agent_key` unique, optional ip/os/version/fingerprint/metadata.
- `Api\AgentHeartbeatRequest`, `AgentHeartbeatApiRequest`, `AgentPollRequest`, `AgentResultRequest`, `FetchScriptRequest` — protocol payloads.
- `Payment\StorePaymentDetailsRequest` — validates `product_type ∈ {service,course,module,lesson}`, `product_id` exists (custom after-hook per type), `account_name_number`, `transaction_amount`, `transaction_id_reference`, optional `notes`.
- `CommandCenter\StoreAgentCommandRequest`, `CancelAgentCommandRequest`.
- `Service\StoreServiceRequest`, `UpdateServiceRequest`.
- `Auth\LoginRequest`, `ProfileUpdateRequest`.

### Policies (Laravel convention auto-discovery)
- `AgentCommandPolicy` — all abilities require `is_admin` (used by CommandCenterController via `$this->authorize`).
- `CoursePolicy::view`, `ModulePolicy`, `LessonPolicy` — delegate to `EntitlementService`.

### Jobs
- `DispatchAgentCommandJob` — currently a **no-op stub** (only checks command is still `queued`).
- `ProcessAgentResultJob` — sets command status to `succeeded`/`failed` from `result_status` + `finished_at = now()`.

### Console Command
- `agents:check-offline {--minutes=5}` — marks `AgentStatus` records `online` but stale (last_heartbeat older than threshold or null) as `offline` and sends a Telegram alert. Scheduled every 5 minutes.

---

## 10. Controllers & Views

### Controllers of note
- **PaymentController** — `showCheckout($slug, Request)` resolves product type from route name (`services/courses/modules/lessons`), loads approved/pending payments for the user, renders `payments.checkout` with `mockSlug`. `storePayment` validates submitted amount equals product price ±0.01 SYP, creates `pending` Payment, sends Telegram "New Payment Request". `mockGlobalPaymentSuccess` creates + immediately approves a payment (dev shortcut).
- **UserToolController** — `index` lists approved service payments, **deletes old `agent-api` tokens and creates a fresh Sanctum token**, passes `baseUrl` + token to `my-tools` view. `downloadAgent` verifies approved payment (or `ADMIN-TEST-MODE` for admins) then **streams a generated Python bootstrapper** (`agent_bootstrapper.py`) with token/license/service-id baked in — 3 phases: setup, fetch (`/api/fetch-script`), execute (`exec`), then heartbeat loop every 60s.
- **AgentApiController** (legacy API) — heartbeat requires approved license (admin exempt), upserts `AgentStatus`, Telegram alert on first connect; `fetchScript` validates license + non-expired (admin bypass key `ADMIN-TEST-MODE`), returns `script_code`; 404 if no script configured.
- **AgentController** (v1) — thin wrapper over `AgentProtocolService` with FormRequest validation; returns command `{uuid,key,payload,signature_hash,nonce,expires_at}` on poll.
- **AcademyController** — admin CRUD for Course/Module/Lesson incl. local video upload via `SecureVideoUpload`, unique slug generation per module.
- **CourseController** — public catalog/detail/lesson pages with entitlement gating (403-style redirect for locked lessons).
- **Admin\PaymentController** — paginated list w/ status filter, show, approve/reject (only from `pending`), delegates to `PaymentVerificationService`.
- **Admin\SubscriptionController** — `/admin/subscriptions` analytics report (stat cards, status badges, filters, search, pagination). See §5.1.
- **Admin\CommandCenterController** — lists agents + paginated commands; store/cancel with policy checks.
- **Admin\ServiceController** — resource CRUD via `ServiceManagementService`.
- **AdminController** — dashboard stats: services, lessons, pending payments, courses count, online agents; plus a quick "add lesson" action.
- **ContactController** — `show` renders the Cyber-Dark contact page; `store` validates the enquiry and forwards it to Telegram (best-effort).

### Authentication flow

Post-login routing is **role-aware** (`AuthenticatedSessionController::store`):

| User | Redirect |
|---|---|
| `is_admin === true` | `/admin/dashboard` (`route('dashboard')`) |
| `is_admin === false` | `/` (`route('home')`) |

A previously intended URL (e.g. a protected page the visitor was bounced from) still takes
priority via `redirect()->intended($default)`.

> The previous build sent *every* user to `route('dashboard')`, which is the admin-only
> `/admin/dashboard` endpoint — so non-admins landed on a 403 immediately after logging in.

**Registration does not auto-login.** `RegisteredUserController::store` creates the user,
fires the `Registered` event (preserving Breeze email verification), then redirects to
`/login` with the flash message **"Registration successful! Please log in."**. The login
screen shows a "Don't have an account? Register" CTA linking to `/register`.

### Blade views (57 files, `resources/views/`)
- **Layouts:** `layouts/app.blade.php` (public, "Cyber-Dark" theme), `layouts/admin.blade.php` (admin panel, with a **Subscriptions** sidebar link), `layouts/guest.blade.php`, `layouts/navigation.blade.php`.
- **Public:** `welcome` (landing), `about` (Cyber-Dark redesign describing the Blue/Red/Cloud agent families), `contact` (Cyber-Dark redesign: support channel cards + validated enquiry form), `services` (branded **"مصفوفة الخدمات السيبرانية / Cybersecurity Services Matrix"** headline + Alpine.js category filtering), `service-details`, `courses/index`, `courses/show`, `lessons/show`, `my-tools`, `dashboard` (admin), `payments/checkout`.
- **Admin:** `admin/acade/index`, `admin/command-center/index`, `admin/courses/{edit,show}`, `admin/lessons/edit`, `admin/modules/edit`, `admin/payments/{index,show}`, **`admin/subscriptions/index`**, `admin/services/{_form,create,edit,index,show}`.
- **Auth (Breeze):** login (with register CTA), register, forgot-password, reset-password, confirm-password, verify-email.
- **Profile:** edit + partials (update-profile, update-password, delete-user).
- **Components:** application-logo, auth-session-status, buttons, dropdown, input-*, modal, nav-links (Alpine.js driven).

### Services page category filtering

`/services` filters client-side with Alpine.js (no page reload, grid layout preserved):

- `x-data` holds `activeCategory` (initialised from `?category=`, validated against the
  real category list) plus a `matches(category)` predicate.
- Each card is wrapped in `x-show="matches(@js($service->category))"` with a short opacity
  transition; the active filter button is highlighted via `:class` binding.
- Per-category counts are rendered server-side, and a "no agents in this category" hint
  appears when a filter yields no cards.

**Frontend style:** Tailwind with brand color `karam-green #008751` (utility class; `#00f260` used for glow accents), Figtree font, `@tailwindcss/forms`. `.cursorrules` mandates a "Cyber-Dark" admin theme.


---

## 11. Tests (`tests/`, PHPUnit 11)

| File | Covers |
|---|---|
| `Feature/SaasPlatformTest.php` | admin approves payment → licence issued; user submits receipt (DB pending); `/api/fetch-script` 403 w/o licence → 200 with valid licence; **generated bootstrapper** is valid Python with all placeholders resolved + licence baked in; download rejected without an approved licence; **seeded catalog** has exactly 10 automated services with correct category/price/script/details; **mock-payment bypass returns 404 in production**; USD price + label derivation; **API endpoints authenticated & rate limited**; HTML sanitizer strips tags/attributes; storefront + `/my-tools` render badges and `expires_at`; **contact form validation**; **about page branding**; **services page category filters**; **admin subscriptions report** (access control, rendering, status filters, licence/email search) |
| `Feature/CommandCenterAndAgentFlowTest.php` | admin queues command; non-admin 403; **full agent flow**: register → poll → result; entitlement gates academy endpoints (403 → 200 after Entitlement created) |
| `Feature/ExampleTest`, `Unit/ExampleTest` | framework defaults |
| `Feature/ProfileTest` | profile update/delete |
| `Feature/Auth/*` (6 files) | Breeze: authentication, email verification, password confirmation/reset/update, registration. Includes **role-aware login redirects** (admin → `/admin/dashboard`, user → `/`) and **registration that does not auto-login** (redirects to `/login` with a flash status) |

**Test env** (`phpunit.xml`): MySQL test database `cyber_with_karam_test`, sync queue, array cache/session/mail, `BCRYPT_ROUNDS=4`.

Current status: **48 tests / 278 assertions — all passing.**

---

## 12. The Python Agent

### `agent.py` (repo root — legacy/demo loop)
- Reads `SANCTUM_TOKEN` env var; exits if missing.
- Loop (every 300s): `POST /api/heartbeat` (service_id=1, status online, timestamp) then `GET /api/fetch-script` (service_id=1, licence `CWK-TEST-KEY-001`) and **`exec(script_code)`**.
- Handles 403 "expired" messages by exiting with a renewal warning.
- ⚠️ Contains a commented security warning: `exec()` on remote code is dangerous — only acceptable because the API is trusted/licensed.

### `agent_bootstrapper.py` — dynamically generated, per-licence

**Source template:** `stubs/agent_bootstrapper.py` (committed to the repo).
**Generation:** `UserToolController::downloadAgent()` →
`buildAgentBootstrapper()` reads the stub and substitutes placeholders with
`strtr()`:

| Placeholder | Value |
|---|---|
| `@BASE_URL@` | `rtrim(config('app.url'), '/')` |
| `@SANCTUM_TOKEN@` | a **fresh** Sanctum token (`agent-bootstrapper`), also overridable via the `SANCTUM_TOKEN` env var |
| `@SERVICE_ID@` | the requested service id |
| `@LICENSE_KEY@` | the active licence key |
| `@SERVICE_TITLE@` / `@SERVICE_CATEGORY@` | service metadata (escaped via `addcslashes`) |
| `@LICENSE_EXPIRES_AT@` | `Payment.expires_at` (ISO-8601) |

Using `strtr()` (rather than a PHP heredoc) means the Python body is **never**
re-interpreted by PHP, so `$`, `{}` and backslashes inside the script are safe.
The response streams as an attachment with `Content-Type: text/x-python` plus
`Cache-Control: no-store` (it embeds a live token).

**Execution lifecycle** — identical on Linux, macOS and Windows:

| Phase | Function | Behaviour |
|---|---|---|
| **1 — Dependencies** | `phase_dependencies()` | Requires Python ≥ 3.8; imports every stdlib module it needs; if `requests` is missing it retries `pip install` via `python -m pip`, `python -m pip --user`, `pip3`, `pip`. Exits `4` if it still cannot be installed |
| **2 — Licence check & fetch** | `fetch_script()` | `GET /api/fetch-script?service_id=&license_key=` with `Authorization: Bearer <token>`. Prints service/expiry context |
| **3 — Safe execution** | `execute_payload()` | `exec(compile(code, "<cyberlogia-payload>", "exec"), namespace)`. `SystemExit` is caught, all other exceptions are logged with a traceback — **a payload error never terminates the agent** |
| **4 — Expiry / cancellation** | (inside `fetch_script`) | HTTP **403** → prints "Agent Terminated — Renewal Required" with the renewal steps and `sys.exit(3)`. HTTP 401 → re-download hint, exit 3. HTTP 404 → contact support, exit 3. Other non-200 → exit 5 |
| **5 — Heartbeat loop** | `send_heartbeat()` | `POST /api/heartbeat` every **60s** (`HEARTBEAT_INTERVAL`, mirrors `config('cyberlogia.agent_heartbeat_interval')`) with `service_id` + best-effort IP. Runs until `Ctrl+C` |

**Exit codes:** `0` clean stop · `3` licence problem · `4` dependency problem ·
`5` network/API problem.

**Token handling:** the Sanctum token is only ever placed in the `Authorization`
header — it is never printed to the console, and the licence key is sent as a
query parameter (therefore `no-store` response headers are applied server-side).

---

## 13. End-to-End Flows

### Flow A — Buy a security agent & run it
1. Visitor browses `/services` (category tags, "Automated Agent Service" badges, SYP + USD pricing) → `/services/{slug}` (HTML docs, 4-step how-it-works, installation guide) → Pay → `/services/{slug}/pay` (auth+verified).
2. Checkout form: account name/number, transaction amount (must match price ±0.01), reference ID, notes → `POST /payment/submit` → Payment `pending` → Telegram alert to admin.
3. Admin: `/admin/payments` → show → **Approve** → licence `CWK-…` generated, `expires_at = now()+30d`, ActionLog + Telegram message.
4. User: `/my-tools` → shows each active subscription with `expires_at`, days remaining, status pill, licence key and a **Download `agent_bootstrapper.py`** button (plus copy-paste curl/PowerShell one-liners). Visiting the page issues a fresh `agent-api` Sanctum token.
5. `GET /my-tools/download-agent/{service_id}/{license_key}` → streams `agent_bootstrapper.py` with base URL, fresh Sanctum token, service id, licence key and expiry baked in (`Cache-Control: no-store`).
6. Agent lifecycle: dependency check → `GET /api/fetch-script` (403 if licence invalid/expired) → `exec()` the `script_code` stored on the Service → `POST /api/heartbeat` every 60s. On 403 the agent prints renewal instructions and exits with code 3.

### Flow B — Command Center (v1 agent protocol)
1. Agent `POST /api/v1/agents/register` (Sanctum) → gets one-time `api_token` (only sha256 stored server-side).
2. Admin `/admin/command-center` → `POST /admin/command-center/commands` (policy-checked) → `CommandDispatchService` signs (HMAC) + queues command (TTL default 300s).
3. Agent polls `POST /api/v1/agents/poll` with `X-Agent-Key` + Bearer → receives command or `null`; command moves `queued → sent`.
4. Agent executes and `POST /api/v1/agents/result` with `command_uuid` + unique `nonce` (409 on replay) → result stored → `ProcessAgentResultJob` marks command `succeeded`/`failed`.
5. Admin can cancel non-terminal commands (`cancelled` + reason).
6. Stale presence: `agents:check-offline` (every 5 min) flips `agent_statuses.online → offline` + Telegram alert.

### Flow C — Academy purchase
1. `/courses` → `/courses/{slug}` → checkout route (service/course/module/lesson variants all reuse `PaymentController@showCheckout`).
2. Receipt submitted → pending → admin approves → **Entitlement** created (30 days).
3. `EntitlementService` gates `/academy/*` JSON endpoints, `courses/show`, `lessons/show` (free lessons always visible; admin bypasses all).


---

## 14. Security Mechanisms (and gaps)

**Implemented:**
- `is_admin` middleware + policies on all admin/command routes; Breeze auth + email verification.
- Sanctum-style token hashing for v1 agents (`sha256` at rest, `hash_equals` compare); one-time plain token at register.
- Command integrity: HMAC-SHA256 signature with `APP_KEY`, UUID nonce, expiry TTL, replay detection via `last_nonce` (409).
- Licence keys: `CWK-` + 16 high-entropy chars, uniqueness enforced; expiry checked at script-fetch time.
- Receipt amount must match product price (±0.01) to prevent underpayment.
- HTML sanitization of `Service.full_description`: **tag allow-list plus full attribute stripping** (blocks `onclick=` / event-handler injection and `javascript:` URLs) before the value is rendered as HTML on the public service page.
- Video upload extension+MIME validation, safe hashed filenames, path-traversal rejection.
- CSRF exemption limited to `api/*`; FormRequest validation everywhere; all DB access goes through Eloquent/Query Builder, so queries are parameter-bound (no raw string interpolation).
- Audit logging (`action_logs`) for payment approve/reject.
- Telegram notifications are **best-effort**: `ConnectionException` is caught and logged so a DNS/outage can never fail a payment approval.

### Hardened during the QA pass

| Area | Hardening |
|---|---|
| **Payment bypass (critical)** | `PaymentController::mockGlobalPaymentSuccess()` self-approved payments and minted real licences for any logged-in user. Now `abort_if(app()->environment('production'), 404)`, and the "Pay Securely Now" button is replaced by manual-verification copy in production. Covered by a regression test that asserts 404 + zero payments. |
| **Rate limiting** | Added `agent-auth` (30/min + 500/hour, keyed by a hashed `X-Agent-Key`) and `agent-script` (10/min per user) limiters; `/api/fetch-script` returns executable code and accepts a brute-forceable licence key, so it is now throttled harder than the rest of the API. |
| **Credential caching** | `/api/fetch-script` and the agent download now send `Cache-Control: no-store, private`, `Pragma: no-cache`, `Referrer-Policy: no-referrer`, `X-Content-Type-Options: nosniff` — the licence key travels in the URL (→ access logs) and the download body embeds a live token. |
| **Stored XSS** | `Service::setFullDescriptionAttribute()` now strips **all** attributes, not just disallowed tags, because `full_description` is rendered as HTML on `/services/{slug}`. |
| **Resilience** | Telegram network failures no longer propagate and break core flows. |
| **UI correctness** | Checkout/my-tools no longer instruct users to run `agent.py`; they reference the generated `agent_bootstrapper.py`. |

**Known gaps / notes (be aware when working on the project):**
- Two parallel agent systems coexist: the licensed legacy API (`/api/heartbeat`, `/api/fetch-script` with Sanctum + licence key) and the stronger v1 protocol (`X-Agent-Key` + hashed token).
- Admin bypass key `ADMIN-TEST-MODE` exists in code for `fetchScript` and agent download.
- `DispatchAgentCommandJob` / `ProcessAgentResultJob` are minimal (dispatch job is effectively a stub).
- `payment.mock-global-success` still exists for local development but now **404s in production**; delete the route entirely for extra assurance.
- `/my-tools` regenerates the Sanctum `agent-api` token on every page load (deletes prior ones), invalidating running agents' tokens.
- Currency is **SYP**; the USD figure shown throughout is derived from the nominal `cyberlogia.syp_per_usd` rate and is indicative only.
- Licence keys are sent as a **query parameter** (API contract), so they will appear in web-server access logs — redact these in production log handling.
- Multiple `lessons` schema-fix migrations indicate past churn — verify migration state before adding new ones.

---

## 15. Conventions & AI Rules (`.cursorrules`)

The repo carries explicit AI-coding rules:
1. **Communication:** be concise, incremental edits only ("..." for unchanged code), no hallucinated variables/models — ask instead.
2. **Context:** only scan files referenced with `@`; never index `vendor/`, `node_modules/`, `storage/`, `public/assets/`.
3. **Standards:** Laravel 12 best practices (service classes, type-hinting, Sanctum for API); check SQL injection, mass assignment, and always apply `is_admin` middleware to admin routes; **Python agent must stay dependency-free (only `requests`)**.
4. **Branding:** Admin UI = "Cyber-Dark" Tailwind theme; payments manual (receipt upload) with `pending/approved` logic; license keys via `Str::uuid()` or high-entropy string.
5. Acknowledge with "Rules Loaded".

---

## 16. How to Run

```bash
composer setup      # install + .env + key + migrate + npm install/build
composer dev        # serve + queue + pail logs + vite (concurrently)
# or manually:
php artisan serve
php artisan queue:listen --tries=1
php artisan schedule:work        # for agents:check-offline
npm run dev
composer test       # run PHPUnit
```

- Seeder: `php artisan db:seed` → clears `services` and re-creates the **10 automated CyberLogia agents** (EDR & Threat Hunting, CIS Hardening, Log Collector/SIEM, FIM, Ransomware/Breach Simulation, Internal Vuln Scanner, Local App Misconfig, Cloud Sandbox/Attachment Auditor, Cloud CIS Benchmarking, K8s & Docker Auditor) across **Blue Team / Red Team / Cloud Security**. Idempotent — re-running always leaves exactly 10 rows.
- Create an admin: set `is_admin = true` on a user row (no seeder does this automatically).
- Telegram: set `TELEGRAM_BOT_TOKEN` + `TELEGRAM_CHAT_ID` (already present in local `.env`). Verify with `php artisan telegram:test`; use `php artisan telegram:test -- --resolve` if you need to find the numeric chat id.
- Admin analytics: `/admin/subscriptions` (`php artisan route:list --name=admin.subscriptions` to confirm the route).

---

## 17. Quick File Index for AI Agents

| If you need to… | Read |
|---|---|
| Understand routing | `routes/web.php`, `routes/api.php`, `routes/auth.php`, `routes/console.php`, `bootstrap/app.php` |
| Payment logic | `app/Services/Payment/PaymentVerificationService.php`, `app/Http/Controllers/PaymentController.php`, `app/Http/Controllers/Admin/PaymentController.php` |
| Academy access rules | `app/Services/Academy/EntitlementService.php`, `app/Http/Controllers/CourseController.php` |
| Agent v1 protocol | `app/Services/Agent/AgentProtocolService.php`, `app/Http/Controllers/Api/AgentController.php`, `app/Http/Middleware/AuthenticateAgent.php` |
| Legacy script/heartbeat API | `app/Http/Controllers/Api/AgentApiController.php`, `agent.py` |
| Command center | `app/Services/CommandCenter/*`, `app/Http/Controllers/Admin/CommandCenterController.php`, `app/Models/AgentCommand.php` |
| Telegram alerts | `app/Services/Telegram/TelegramService.php`, `config/telegram.php` |
| Schema | `database/migrations/*`, `app/Models/*` |
| Admin UI style | `resources/views/layouts/admin.blade.php`, `tailwind.config.js` |
| **Agent download generator** | `app/Http/Controllers/UserToolController.php` + template `stubs/agent_bootstrapper.py` |
| **Rate limiters** | `app/Providers/AppServiceProvider.php` (`api`, `agent-auth`, `agent-script`) |
| **Seeded service catalog** | `database/seeders/DatabaseSeeder.php`, `app/Models/Service.php` (`usdPrice()`, `priceLabel()`, HTML sanitizer) |
| **Brand + FX config** | `config/cyberlogia.php` |
| **Storefront views** | `resources/views/services.blade.php`, `resources/views/service-details.blade.php`, `resources/views/my-tools.blade.php` |
| Tests / expected behavior | `tests/Feature/SaasPlatformTest.php`, `tests/Feature/CommandCenterAndAgentFlowTest.php` |

---

## 18. Action-to-Code Traceability Trees

This section maps every major user/admin action through the full stack:

**Front-end (Route → View)** → **Controller** → **(Service)** → **Model** → **Database table**

Use it to locate every file touched by a given action, front-end to back-end.

### Legend

| Symbol | Meaning |
|--------|---------|
| `└─` / `├─` | Tree branch (parent → child call) |
| `[Route]` | Route definition + name (see `routes/web.php`, `routes/api.php`, `routes/auth.php`) |
| `{View}` | Blade template in `resources/views/` |
| `Controller` | `app/Http/Controllers/...` |
| `Service` | `app/Services/...` |
| `Model` | `app/Models/...` |
| `DB` | Database table (see `database/migrations/`) |
| `Job` | `app/Jobs/...` (queued) |
| `File` | Non-DB file written/read (e.g. Python agent template) |

---

### 18.1 Public: Browse Homepage & Services Catalog

```
Browse Services / Homepage
├─ [Route]  GET /  (name: home)          routes/web.php
│           GET /services (name: services) routes/web.php
│           (closure controller inline in web.php)
│           Model: App\Models\Service
│           DB:    services
└─ {View}   resources/views/welcome.blade.php
            resources/views/services.blade.php
            resources/views/layouts/app.blade.php   (shared layout)
            resources/views/layouts/navigation.blade.php
            resources/views/components/*             (reusable UI bits)
```

---

### 18.2 Public + Auth: View Service Details

```
View Service Details (/services/{slug})
├─ [Route]  GET /services/{service:slug}  (name: service.show)  routes/web.php
│           (closure controller inline in web.php)
│           ├─ Model: App\Models\Service              (route-model binding by slug)
│           ├─ Model: App\Models\Payment              (approved payment for this user+service)
│           │   DB: payments
│           └─ DB:    services
├─ Service: App\Services\Payment\PaymentVerificationService
│           (userHasApprovedAccess / getApprovedPayment helpers)
└─ {View}   resources/views/service-details.blade.php
            resources/views/layouts/app.blade.php
```

---

### 18.3 Customer: Checkout & Submit Manual Payment

```
Checkout & Submit Payment (service / course / module / lesson)
├─ [Route]  GET  /services/{slug}/pay             (name: services.pay)     routes/web.php
│           GET  /courses/{slug}/checkout         (name: courses.checkout)
│           GET  /academy/modules/{id}/checkout   (name: modules.checkout)
│           GET  /academy/lessons/{slug}/checkout (name: lessons.checkout)
│           POST /payment/submit                  (name: payments.submit)
│           (auth + verified middleware)
│
├─ Controller: App\Http\Controllers\PaymentController
│   ├─ showCheckout()        → shows the checkout form
│   │   ├─ Model: App\Models\Service / Course / Module / Lesson
│   │   │   DB: services / courses / modules / lessons
│   │   ├─ Model: App\Models\Payment              (approved/pending for this user)
│   │   │   DB: payments
│   │   └─ {View} resources/views/payments/checkout.blade.php
│   └─ storePayment()        → validates receipt, creates pending Payment, sends Telegram
│       ├─ Request: App\Http\Requests\Payment\StorePaymentDetailsRequest
│       ├─ Service: App\Services\Payment\PaymentVerificationService
│       │   (creates Payment, status = pending)
│       ├─ Service: App\Services\Telegram\TelegramService  (new receipt alert)
│       ├─ Model: App\Models\Payment              DB: payments
│       ├─ Model: App\Models\Service / Course / Module / Lesson  (price lookup)
│       └─ redirect back to checkout page
│
└─ (Optional dev-only mock)
    [Route] GET /payment/mock-success/{type}/{slug}  (name: payment.mock-global-success)
    Controller: PaymentController@mockGlobalPaymentSuccess  (404 in production)
    Service: PaymentVerificationService@approve  (mints license key + Entitlement)
```
---

### 18.4 Admin: Approve / Reject a Payment

```
Approve / Reject Payment (Admin Panel)
├─ [Route]  GET    /admin/payments                    (name: admin.payments.index)   routes/web.php
│           GET    /admin/payments/{payment}          (name: admin.payments.show)
│           POST   /admin/payments/{payment}/approve  (name: admin.payments.approve)
│           POST   /admin/payments/{payment}/reject   (name: admin.payments.reject)
│           (auth + verified + admin middleware)
│
├─ Controller: App\Http\Controllers\Admin\PaymentController
│   ├─ index()     → list payments  → {View} resources/views/admin/payments/index.blade.php
│   ├─ show()      → single payment → {View} resources/views/admin/payments/show.blade.php
│   ├─ approve()   → PaymentVerificationService::approve()
│   └─ reject()    → PaymentVerificationService::reject()
│
├─ Service: App\Services\Payment\PaymentVerificationService
│   ├─ approve():  Payment status → approved, expires_at = +30 days,
│   │              license_key = CWK-<16 chars> (services only),
│   │              Entitlement::updateOrCreate() (courses/modules/lessons),
│   │              ActionLogService::log()  → DB: action_logs
│   │              TelegramService::sendMessage()  (approval alert)
│   └─ reject():   Payment status → rejected, ActionLogService::log()
│
├─ Model: App\Models\Payment              DB: payments
├─ Model: App\Models\User                 DB: users
├─ Model: App\Models\Service / Course / Module / Lesson  (product title lookup)
├─ Model: App\Models\Entitlement          DB: entitlements
├─ Model: App\Models\ActionLog            DB: action_logs
└─ Telegram: config/telegram.php + App\Services\Telegram\TelegramService
```

---

### 18.5 Customer: My Tools & Download Agent Bootstrapper

```
My Tools / Download Agent (/my-tools, /my-tools/download-agent/{id}/{key})
├─ [Route]  GET  /my-tools                                    (name: my-tools.index)  routes/web.php
│           GET  /my-tools/download-agent/{service_id}/{license_key}  (name: my-tools.download-agent)
│           (auth middleware for index; download verified by licence key only, no session)
│
├─ Controller: App\Http\Controllers\UserToolController
│   ├─ index()  → list approved service payments, (re)generate Sanctum agent-api token
│   │   ├─ Model: App\Models\Payment       DB: payments  (with 'service')
│   │   ├─ Model: App\Models\Service       DB: services
│   │   ├─ Model: App\Models\User          DB: users  (personal_access_tokens)
│   │   └─ {View} resources/views/my-tools.blade.php
│   └─ downloadAgent()  → validate licence key (approved, not expired)
│       ├─ Model: App\Models\Payment       DB: payments
│       ├─ Model: App\Models\Service       DB: services
│       ├─ File:  stubs/agent_bootstrapper.py  (rendered Python agent)
│       └─ response: text/x-python attachment agent_bootstrapper.py
│
└─ DB:    payments, services, personal_access_tokens
```

---

### 18.6 Agent API (Legacy, Sanctum): Fetch Script / Heartbeat / Token

```
Licensed Agent API (Sanctum bearer token)
├─ [Route]  GET  /api/fetch-script     (throttle: agent-script)   routes/api.php
│           POST /api/heartbeat        (throttle: api)
│           POST /api/agent/token
│           (auth:sanctum middleware)
│
├─ Controller: App\Http\Controllers\Api\AgentApiController
│   ├─ fetchScript()   → returns executable Python script_code for the licence
│   │   ├─ Model: App\Models\Payment        DB: payments  (licence + expiry check)
│   │   ├─ Model: App\Models\Service        DB: services  (script_code payload)
│   │   └─ Model: App\Models\AgentHeartbeat DB: agent_heartbeats
│   ├─ heartbeat()     → record agent heartbeat
│   │   └─ Model: App\Models\AgentHeartbeat DB: agent_heartbeats
│   └─ createToken()   → issue Sanctum personal access token
│       └─ DB: personal_access_tokens
│
└─ DB: payments, services, agent_heartbeats, personal_access_tokens
```
---

### 18.7 Agent v1 Protocol: Register / Heartbeat / Poll / Result

```
Agent v1 Protocol (X-Agent-Key + hashed bearer token)
├─ [Route]  POST /api/v1/agents/register   routes/api.php
│           POST /api/v1/agents/heartbeat
│           POST /api/v1/agents/poll
│           POST /api/v1/agents/result
│           (prefix v1/agents, throttle: agent-auth, middleware agent.auth)
│           (legacy alias: /api/v1/agent/*)
│
├─ Controller: App\Http\Controllers\Api\AgentController
│   ├─ register()   → Agent::create / update  DB: agents
│   ├─ heartbeat()  → AgentHeartbeat::create  DB: agent_heartbeats
│   ├─ poll()       → fetch pending AgentCommand  DB: agent_commands
│   └─ result()     → AgentCommandResult::create  DB: agent_command_results
│
├─ Service: App\Services\Agent\AgentProtocolService
├─ Model: App\Models\Agent              DB: agents
├─ Model: App\Models\AgentCommand       DB: agent_commands
├─ Model: App\Models\AgentCommandResult DB: agent_command_results
├─ Model: App\Models\AgentHeartbeat     DB: agent_heartbeats
├─ Model: App\Models\AgentStatus        DB: agent_statuses
└─ Middleware: App\Http\Middleware\AuthenticateAgent
```

---

### 18.8 Admin: Command Center – Queue / Cancel Command

```
Command Center (Admin Fleet Management)
├─ [Route]  GET     /admin/command-center                          (name: admin.command-center.index)  routes/web.php
│           POST    /admin/command-center/commands                 (name: admin.command-center.commands.store)
│           POST    /admin/command-center/commands/{command}/cancel (name: admin.command-center.commands.cancel)
│           (auth + verified + admin middleware)
│
├─ Controller: App\Http\Controllers\Admin\CommandCenterController
│   ├─ index()   → list agents + commands
│   │   ├─ Model: App\Models\Agent           DB: agents
│   │   ├─ Model: App\Models\AgentCommand    DB: agent_commands  (with result, requester)
│   │   └─ {View} resources/views/admin/command-center/index.blade.php
│   ├─ store()   → CommandDispatchService::dispatch()  (queues command)
│   └─ cancel()  → CommandDispatchService::cancel()
│
├─ Service: App\Services\CommandCenter\CommandDispatchService
│           App\Services\CommandCenter\CommandSigningService
│
├─ Job:  App\Jobs\DispatchAgentCommandJob   App\Jobs\ProcessAgentResultJob
│
├─ Model: App\Models\Agent           DB: agents
├─ Model: App\Models\AgentCommand    DB: agent_commands
└─ Policy: App\Policies\AgentCommandPolicy
```

---

### 18.9 Customer: Browse Academy Courses & Lessons

```
Academy Storefront (Courses → Modules → Lessons)
├─ [Route]  GET  /courses                              (name: courses)                 routes/web.php
│           GET  /courses/{course:slug}                (name: courses.show)
│           GET  /courses/{course:slug}/lessons/{lesson:slug}  (name: lessons.show)
│
├─ Controller: App\Http\Controllers\CourseController
│   ├─ index()     → active courses  → {View} resources/views/courses/index.blade.php
│   │   └─ Model: App\Models\Course          DB: courses  (with modules.lessons)
│   ├─ show()      → course detail   → {View} resources/views/courses/show.blade.php
│   │   ├─ Model: App\Models\Course          DB: courses
│   │   └─ Service: App\Services\Academy\EntitlementService  (userHasCourseAccess)
│   └─ showLesson()→ lesson detail  → {View} resources/views/lessons/show.blade.php
│       ├─ Model: App\Models\Course   DB: courses
│       ├─ Model: App\Models\Lesson   DB: lessons  (with module.course)
│       └─ Service: EntitlementService  (userHasLessonAccess; is_free bypass)
│
├─ Model: App\Models\Course     DB: courses
├─ Model: App\Models\Module     DB: modules
├─ Model: App\Models\Lesson     DB: lessons
├─ Model: App\Models\Entitlement  DB: entitlements  (access check)
└─ Service: App\Services\Academy\EntitlementService
```
---

### 18.10 Admin: Academy CRUD (Course / Module / Lesson)

```
Admin Academy (Course / Module / Lesson CRUD)
├─ [Route]  (all under /admin/academy, auth + verified + admin)  routes/web.php
│   GET    /admin/academy                                        (admin.academy.index)
│   POST   /admin/academy/course/store                           (admin.course.store)
│   GET    /admin/academy/course/{course}/edit                   (admin.course.edit)
│   PUT    /admin/academy/course/{course}                        (admin.course.update)
│   DELETE /admin/academy/course/{course}                        (admin.course.delete)
│   GET    /admin/academy/courses/{course}                       (admin.course.show)
│   POST   /admin/academy/module/store                           (admin.module.store)
│   GET    /admin/academy/module/{module}/edit                   (admin.module.edit)
│   PUT    /admin/academy/module/{module}                        (admin.module.update)
│   DELETE /admin/academy/module/{module}                        (admin.module.delete)
│   POST   /admin/academy/lesson/store                           (admin.lesson.store)
│   GET    /admin/academy/lesson/{lesson}/edit                   (admin.lesson.edit)
│   PUT    /admin/academy/lesson/{lesson}                        (admin.lesson.update)
│   DELETE /admin/academy/lesson/{lesson}                        (admin.lesson.delete)
│
├─ Controller: App\Http\Controllers\AcademyController
│   ├─ index()   → {View} resources/views/admin/acade/index.blade.php
│   │   └─ Model: App\Models\Course  DB: courses  (with modules.lessons)
│   ├─ storeCourse()/updateCourse()/destroyCourse()
│   │   └─ Model: App\Models\Course  DB: courses
│   ├─ storeModule()/updateModule()/destroyModule()
│   │   └─ Model: App\Models\Module  DB: modules
│   ├─ storeLesson()/updateLesson()/destroyLesson()
│   │   ├─ Model: App\Models\Lesson  DB: lessons
│   │   ├─ Service: App\Support\SecureVideoUpload  (local video file storage)
│   │   └─ File: storage/app/*  (uploaded video files)
│   └─ {View} resources/views/admin/courses/{show,edit}.blade.php
│             resources/views/admin/modules/edit.blade.php
│             resources/views/admin/lessons/edit.blade.php
│
├─ Controller: App\Http\Controllers\Admin\CourseController  (show only)
│   └─ Model: App\Models\Course  DB: courses
│
├─ Model: App\Models\Course   DB: courses
├─ Model: App\Models\Module   DB: modules
├─ Model: App\Models\Lesson   DB: lessons
└─ Policy: App\Policies\{CoursePolicy, ModulePolicy, LessonPolicy}
```

---

### 18.11 Admin: Services CRUD

```
Admin Services (Service Catalog Management)
├─ [Route]  (resource /admin/services, auth + verified + admin)  routes/web.php
│   GET/POST/PUT/DELETE → names: admin.services.{index,create,store,show,edit,update,destroy}
│
├─ Controller: App\Http\Controllers\Admin\ServiceController
│   ├─ index()   → {View} resources/views/admin/services/index.blade.php
│   │   └─ Model: App\Models\Service  DB: services
│   ├─ create()/store()    → {View} create.blade.php  → ServiceManagementService::create()
│   ├─ show()              → {View} show.blade.php
│   ├─ edit()/update()     → {View} edit.blade.php + _form.blade.php  → ServiceManagementService::update()
│   └─ destroy()           → Model: App\Models\Service  DB: services
│
├─ Service: App\Services\Service\ServiceManagementService
├─ Model: App\Models\Service  DB: services
├─ Request: App\Http\Requests\Service\{StoreServiceRequest, UpdateServiceRequest}
└─ {View} resources/views/admin/services/{index,create,show,edit,_form}.blade.php
```

---

### 18.12 Admin: Subscriptions Report

```
Admin Subscriptions & Analytics (/admin/subscriptions)
├─ [Route]  GET /admin/subscriptions  (name: admin.subscriptions.index)  routes/web.php
│           (auth + verified + admin middleware)
│
├─ Controller: App\Http\Controllers\Admin\SubscriptionController
│   └─ index()  → aggregated payment stats + paginated list
│       ├─ Model: App\Models\Payment     DB: payments  (with user, service)
│       ├─ Model: App\Models\User        DB: users
│       ├─ Model: App\Models\Service     DB: services
│       └─ {View} resources/views/admin/subscriptions/index.blade.php
│
└─ DB: payments, users, services
```

---

### 18.13 Customer: Profile Management

```
Profile (/profile)
├─ [Route]  GET    /profile              (name: profile.edit)   routes/web.php
│           PATCH  /profile              (name: profile.update)
│           DELETE /profile              (name: profile.destroy)
│           (auth middleware)
│
├─ Controller: App\Http\Controllers\ProfileController
│   ├─ edit()   → {View} resources/views/profile/edit.blade.php
│   │             resources/views/profile/partials/*.blade.php
│   │   └─ Model: App\Models\User  DB: users
│   ├─ update() → User::fill/save  DB: users
│   │   └─ Request: App\Http\Requests\ProfileUpdateRequest
│   └─ destroy()→ logout + User::delete  DB: users
│
└─ Model: App\Models\User  DB: users
```
---

### 18.14 Public: Contact Enquiry

```
Contact (/contact)
├─ [Route]  GET  /contact      (name: contact)        routes/web.php
│           POST /contact      (name: contact.submit)
│
├─ Controller: App\Http\Controllers\ContactController
│   ├─ show()   → {View} resources/views/contact.blade.php
│   └─ store()  → validate → TelegramService::sendMessage() (ops channel)
│       └─ Service: App\Services\Telegram\TelegramService
│
└─ {View} resources/views/contact.blade.php
```

---

### 18.15 Public / Guest: Authentication

```
Auth: Register / Login / Password Reset / Email Verify / Logout
├─ [Route]  all in routes/auth.php  (guest / auth middleware groups)
│   register, login, forgot-password, reset-password,
│   verify-email, password.confirm, password.update, logout
│
├─ Controller: App\Http\Controllers\Auth\*
│   ├─ RegisteredUserController     → Model: App\Models\User  DB: users
│   ├─ AuthenticatedSessionController → login/logout (session)
│   ├─ PasswordResetLinkController / NewPasswordController  → password reset
│   ├─ VerifyEmailController / EmailVerification*  → email verification
│   └─ ConfirmablePasswordController / PasswordController   → confirm password / update
│
├─ Model: App\Models\User  DB: users
│         (personal_access_tokens via Sanctum for API)
└─ {View} resources/views/auth/{login,register,forgot-password,reset-password,verify-email,confirm-password}.blade.php
```

---

### 18.16 Admin Dashboard

```
Admin Dashboard (/admin/dashboard)
├─ [Route]  GET /admin/dashboard  (name: dashboard)  routes/web.php
│           (auth + verified + admin middleware)
│
├─ Controller: App\Http\Controllers\AdminController
│   └─ index()  → aggregates stats
│       ├─ Model: App\Models\Service      DB: services
│       ├─ Model: App\Models\Lesson       DB: lessons
│       ├─ Model: App\Models\Payment      DB: payments  (pending count)
│       ├─ Model: App\Models\Course       DB: courses
│       └─ Model: App\Models\AgentStatus  DB: agent_statuses  (online count)
│
└─ {View} resources/views/dashboard.blade.php
```

---

### 18.17 Console (Scheduler): Check Offline Agents

```
Scheduled Command: agents:check-offline (every 5 minutes)
├─ [Route]  routes/console.php  → Schedule::command('agents:check-offline')->everyFiveMinutes()
│
├─ Command: App\Console\Commands\CheckOfflineAgents
│   ├─ Model: App\Models\Agent        DB: agents
│   ├─ Model: App\Models\AgentStatus  DB: agent_statuses
│   └─ Service: App\Services\Telegram\TelegramService  (offline alert)
│
└─ DB: agents, agent_statuses
```

---

### 18.18 Admin: Edit Service Python Script (Dedicated Page)

```
Edit Service Python Script (/admin/services/{service}/script/edit)
├─ [Route]  GET  /admin/services/{service}/script/edit   (name: admin.services.script.edit)  routes/web.php
│           PUT  /admin/services/{service}/script        (name: admin.services.script.update)
│           (auth + verified + admin middleware)
│
├─ Controller: App\Http\Controllers\Admin\ServiceScriptController
│   ├─ edit()   → {View} resources/views/admin/services/script_edit.blade.php
│   │   ├─ Model: App\Models\Service  DB: services  (reads script_path)
│   │   └─ Service: Service::getScriptContent()  → reads file from storage/app/service_scripts/{id}/script.py
│   │         (falls back to script_code DB column if no file path)
│   └─ update() → ServiceScriptService::store()  → writes script file
│       ├─ Request: App\Http\Requests\Service\EditServiceScriptRequest
│       │   (validates .py file upload OR raw script_content text)
│       ├─ Service: App\Services\Service\ServiceScriptService
│       │   Stores privately in storage/app/service_scripts/{service_id}/script.py
│       │   (local disk — NOT publicly accessible via URL)
│       └─ Model: App\Models\Service  → updates script_path column  DB: services
│
├─ File:  storage/app/service_scripts/{service_id}/script.py  (secure, private storage)
├─ Model: App\Models\Service  DB: services  (script_path column)
└─ {View}   resources/views/admin/services/script_edit.blade.php
            (linked from show.blade.php via "Edit Script" button)
            resources/views/layouts/admin.blade.php
```

---

### 18.19 Quick Reference: Model → Database Table

| Model | Database Table | Purpose |
|-------|---------------|---------|
| `App\Models\User` | `users` | Customers & admins |
| `App\Models\Service` | `services` | Marketplace services (10 automated agents) — `script_path` tracks the private script file |
| `App\Models\Course` | `courses` | Academy courses |
| `App\Models\Module` | `modules` | Course modules |
| `App\Models\Lesson` | `lessons` | Course lessons (+ video) |
| `App\Models\Payment` | `payments` | Manual payment receipts, pending→approved→rejected, licence keys |
| `App\Models\Entitlement` | `entitlements` | Academy access grants (course/module/lesson) |
| `App\Models\Purchase` | `purchases` | Purchase records |
| `App\Models\Agent` | `agents` | Registered remote agents (fleet) |
| `App\Models\AgentCommand` | `agent_commands` | Queued admin commands |
| `App\Models\AgentCommandResult` | `agent_command_results` | Command execution results |
| `App\Models\AgentHeartbeat` | `agent_heartbeats` | Agent heartbeats |
| `App\Models\AgentStatus` | `agent_statuses` | Agent online/offline status |
| `App\Models\ActionLog` | `action_logs` | Admin audit log (approve/reject) |

**Other DB tables:** `personal_access_tokens` (Sanctum API tokens), `cache`, `jobs` (Laravel defaults).






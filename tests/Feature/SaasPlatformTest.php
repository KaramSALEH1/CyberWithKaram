<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SaasPlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_generated_agent_bootstrapper_is_valid_python_and_baked_for_service(): void
    {
        $user = User::factory()->create();
        $service = Service::create([
            'title' => 'Automated EDR & Threat Hunting Agent',
            'slug' => 'automated-edr-threat-hunting-agent',
            'category' => 'Blue Team',
            'description' => 'Test',
            'full_description' => '<p>Test</p>',
            'icon' => '🛡️',
            'price' => 80000,
            'is_automated' => true,
            'is_available' => true,
            'script_code' => "print('payload')",
            'payment_instructions' => 'Test instructions',
        ]);

        Payment::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'amount' => 80000,
            'status' => 'approved',
            'license_key' => 'CLG-BOOTSTRAP-TEST-1',
            'approved_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        $response = $this->actingAs($user)->get(
            route('my-tools.download-agent', [
                'service_id' => $service->id,
                'license_key' => 'CLG-BOOTSTRAP-TEST-1',
            ])
        );

        $response->assertOk();
        $response->assertDownload('agent_bootstrapper.py');

        $script = $response->getContent();

        // Configuration is baked in for the requested service / license.
        $this->assertStringContainsString('CyberLogia', $script);
        $this->assertStringContainsString($service->title, $script);
        $this->assertStringContainsString('Blue Team', $script);
        $this->assertStringContainsString('CLG-BOOTSTRAP-TEST-1', $script);
        $this->assertStringContainsString('SERVICE_ID = '.$service->id, $script);
        $this->assertStringContainsString('/api/fetch-script', $script);
        $this->assertStringContainsString('/api/heartbeat', $script);
        $this->assertStringContainsString('HEARTBEAT_INTERVAL = 60', $script);
        $this->assertStringNotContainsString('@SERVICE_ID@', $script);
        $this->assertStringNotContainsString('@SANCTUM_TOKEN@', $script);

        // All five required lifecycle phases are implemented.
        $this->assertStringContainsString('def phase_dependencies', $script);
        $this->assertStringContainsString('def fetch_script', $script);
        $this->assertStringContainsString('def execute_payload', $script);
        $this->assertStringContainsString('def send_heartbeat', $script);
        $this->assertStringContainsString('403', $script);
        $this->assertStringContainsString('Renew', $script);

        // Every placeholder must be resolved.
        $this->assertDoesNotMatchRegularExpression('/@[A-Z_]+@/', $script);

        // The base URL resolves scheme + host dynamically (test host = localhost).
        $this->assertStringContainsString('BASE_URL = "http://localhost"', $script);
    }

    public function test_agent_download_is_rejected_without_approved_license(): void
    {
        $user = User::factory()->create();
        $service = Service::create([
            'title' => 'EDR Agent',
            'slug' => 'edr-agent',
            'category' => 'Blue Team',
            'description' => 'Test',
            'icon' => '🛡️',
            'price' => 80000,
            'is_available' => true,
        ]);

        $uri = route('my-tools.download-agent', [
            'service_id' => $service->id,
            'license_key' => 'NOT-A-REAL-LICENSE',
        ]);

        // Authenticated user with a bogus key -> JSON 403, never an HTML page.
        $authenticated = $this->actingAs($user)->get($uri);
        $authenticated->assertForbidden();
        $authenticated->assertJson(['error' => 'Invalid or expired license key.']);
        $this->assertStringNotContainsString('<!DOCTYPE html>', $authenticated->getContent());

        // Anonymous curl / PowerShell with a bogus key -> same JSON 403, with no
        // login redirect, so no HTML can end up inside the downloaded .py file.
        $anonymous = $this->get($uri);
        $anonymous->assertForbidden();
        $anonymous->assertJson(['error' => 'Invalid or expired license key.']);
        $this->assertStringNotContainsString('<!DOCTYPE html>', $anonymous->getContent());
    }

    public function test_agent_download_with_expired_license_returns_json_403(): void
    {
        $user = User::factory()->create();
        $service = Service::create([
            'title' => 'EDR Agent',
            'slug' => 'edr-agent-expired',
            'category' => 'Blue Team',
            'description' => 'Test',
            'icon' => '🛡️',
            'price' => 80000,
            'is_available' => true,
        ]);

        Payment::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'amount' => 80000,
            'status' => 'approved',
            'license_key' => 'CWK-EXPIRED-TEST-1',
            'approved_at' => now()->subDays(40),
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->get(route('my-tools.download-agent', [
            'service_id' => $service->id,
            'license_key' => 'CWK-EXPIRED-TEST-1',
        ]));

        $response->assertForbidden();
        $response->assertJson(['error' => 'Invalid or expired license key.']);
        $this->assertStringNotContainsString('<!DOCTYPE html>', $response->getContent());
    }

    public function test_agent_download_without_session_cookies_returns_pure_python(): void
    {
        $user = User::factory()->create();
        $service = Service::create([
            'title' => 'Automated EDR & Threat Hunting Agent',
            'slug' => 'automated-edr-no-cookies-agent',
            'category' => 'Blue Team',
            'description' => 'Test',
            'full_description' => '<p>Test</p>',
            'icon' => '🛡️',
            'price' => 80000,
            'is_automated' => true,
            'is_available' => true,
            'script_code' => "print('payload')",
            'payment_instructions' => 'Test instructions',
        ]);

        Payment::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'amount' => 80000,
            'status' => 'approved',
            'license_key' => 'CWK-NO-COOKIES-TEST-1',
            'approved_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        // Simulates Invoke-WebRequest / curl: NO session cookie at all, fetched
        // from an origin with an explicit port (http://localhost:8000).
        $response = $this->get(
            'http://localhost:8000'.route('my-tools.download-agent', [
                'service_id' => $service->id,
                'license_key' => 'CWK-NO-COOKIES-TEST-1',
            ], false)
        );

        $response->assertOk();
        // Symfony's Response::prepare() appends "; charset=utf-8" to any
        // "text/*" Content-Type, so assert the media type rather than an
        // exact byte-for-byte string. The controller still sends the strict
        // "Content-Type: text/x-python" header.
        $this->assertStringStartsWith(
            'text/x-python',
            (string) $response->headers->get('Content-Type'),
            'Download must be served as Python source (Content-Type: text/x-python).'
        );
        $response->assertHeader('Content-Disposition', 'attachment; filename="agent_bootstrapper.py"');

        $script = $response->getContent();

        // Pure Python only: no login/error HTML can be saved into the .py file.
        $this->assertStringStartsWith('#!/usr/bin/env python3', $script);
        $this->assertStringNotContainsString('<!DOCTYPE html>', $script);
        $this->assertStringNotContainsString('<html', $script);

        // The active scheme, host AND port are baked in for the agent runtime.
        $this->assertStringContainsString('BASE_URL = "http://localhost:8000"', $script);
        $this->assertStringContainsString('CWK-NO-COOKIES-TEST-1', $script);
        $this->assertStringContainsString('SERVICE_ID = '.$service->id, $script);
        $this->assertStringContainsString('/api/fetch-script', $script);

        $this->assertIsValidPython($script);
    }

    /**
     * Assert the downloaded agent parses as valid Python.
     *
     * Uses a real interpreter when one is available (python / py -3 / python3);
     * otherwise falls back to structural checks so the suite still runs on
     * machines without Python installed.
     */
    private function assertIsValidPython(string $script): void
    {
        $python = $this->findPythonBinary();

        if ($python === null) {
            $this->assertStringStartsWith('#!/usr/bin/env python3', $script);
            $this->assertStringNotContainsString('<!DOCTYPE html>', $script);

            return;
        }

        $checker = tempnam(sys_get_temp_dir(), 'cwk_py_checker_');
        $target = tempnam(sys_get_temp_dir(), 'cwk_agent_');

        file_put_contents(
            $checker,
            "import ast, sys\nwith open(sys.argv[1], encoding='utf-8') as handle:\n    ast.parse(handle.read())\n"
        );
        file_put_contents($target, $script);

        $output = [];
        $exitCode = 0;
        exec($python.' '.escapeshellarg($checker).' '.escapeshellarg($target).' 2>&1', $output, $exitCode);

        @unlink($checker);
        @unlink($target);

        $this->assertSame(
            0,
            $exitCode,
            "Downloaded agent_bootstrapper.py failed the Python syntax check:\n".implode("\n", $output)
        );
    }

    private function findPythonBinary(): ?string
    {
        foreach (['python', 'py -3', 'python3'] as $binary) {
            $output = [];
            $exitCode = 0;
            exec($binary.' --version 2>&1', $output, $exitCode);

            if ($exitCode === 0) {
                return $binary;
            }
        }

        return null;
    }

    public function test_seeded_catalog_contains_ten_automated_agent_services(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->assertSame(10, Service::count());

        $expected = [
            'automated-edr-threat-hunting-agent' => ['Blue Team', 80000],
            'system-hardening-cis-compliance-check' => ['Blue Team', 50000],
            'automated-log-collector-siem-forwarder-agent' => ['Blue Team', 60000],
            'file-integrity-monitoring-fim-agent' => ['Blue Team', 40000],
            'continuous-ransomware-breach-simulation-agent' => ['Red Team', 100000],
            'automated-internal-network-vulnerability-scanner' => ['Red Team', 250000],
            'local-application-vulnerability-misconfig-agent' => ['Red Team', 70000],
            'virtual-cloud-sandbox-email-attachment-auditor' => ['Cloud Security', 350000],
            'automated-cloud-asset-cis-benchmarking-agent' => ['Cloud Security', 200000],
            'automated-kubernetes-docker-security-auditor-agent' => ['Cloud Security', 150000],
        ];

        foreach ($expected as $slug => [$category, $price]) {
            $service = Service::where('slug', $slug)->first();
            $this->assertNotNull($service, "Missing seeded service: {$slug}");
            $this->assertSame($category, $service->category);
            $this->assertSame((float) $price, (float) $service->price);
            $this->assertTrue($service->is_automated, "{$slug} must be automated");
            $this->assertNotEmpty($service->script_code, "{$slug} needs script_code");
            $this->assertNotEmpty($service->full_description, "{$slug} needs full_description");
            $this->assertNotEmpty($service->payment_instructions, "{$slug} needs payment_instructions");
            $this->assertStringContainsString('def main()', $service->script_code);
        }

        $categories = Service::distinct()->pluck('category')->sort()->values()->all();
        $this->assertSame(['Blue Team', 'Cloud Security', 'Red Team'], $categories);
    }

    public function test_services_and_my_tools_views_render_branding_badges_and_expiry(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        // Public /services index renders categories + automated badges.
        $servicesPage = $this->get(route('services'));
        $servicesPage->assertOk();
        $servicesPage->assertSee('Automated Agent Service');
        $servicesPage->assertSee('Blue Team');
        $servicesPage->assertSee('Red Team');
        $servicesPage->assertSee('Cloud Security');

        // Public service detail page renders for a guest.
        $service = Service::where('slug', 'automated-edr-threat-hunting-agent')->firstOrFail();
        $details = $this->get(route('service.show', $service->slug));
        $details->assertOk();
        $details->assertSee($service->title);
        $details->assertSee('Installation Guide');
        $details->assertSee('Create Account');
        // Seeded HTML documentation must render, not be stripped.
        $details->assertSee('Continuous Automated EDR', false);

        // /my-tools shows the subscription end date and download button.
        $user = User::factory()->create();
        Payment::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'product_type' => 'service',
            'amount' => 80000,
            'status' => 'approved',
            'license_key' => 'CLG-VIEW-TEST-1',
            'approved_at' => now(),
            'expires_at' => now()->addDays(15),
        ]);

        $tools = $this->actingAs($user)->get(route('my-tools.index'));
        $tools->assertOk();
        $tools->assertSee('Subscription Ends (expires_at)');
        $tools->assertSee(now()->addDays(15)->format('Y-m-d'));
        $tools->assertSee('Download agent_bootstrapper.py');
        $tools->assertSee('Automated Agent Service');
        $tools->assertSee('Blue Team');
        $tools->assertSee($service->title);

        // Copy-paste terminal commands use the live scheme/host/port from
        // request()->schemeAndHttpHost() instead of a hardcoded config URL.
        $tools->assertSee('Invoke-WebRequest -Uri "http://localhost/my-tools/download-agent/', false);
        $tools->assertSee('curl -fsSL "http://localhost/my-tools/download-agent/', false);
    }

    public function test_mock_payment_bypass_is_disabled_in_production(): void
    {
        $user = User::factory()->create();
        $service = Service::create([
            'title' => 'EDR Agent',
            'slug' => 'edr-agent-prod',
            'category' => 'Blue Team',
            'description' => 'Test',
            'icon' => '🛡️',
            'price' => 80000,
            'is_available' => true,
        ]);

        // In production the dev shortcut must not exist at all.
        $this->app['env'] = 'production';

        $this->actingAs($user)->get(
            route('payment.mock-global-success', ['type' => 'service', 'slug' => 'edr-agent-prod'])
        )->assertNotFound();

        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('services', 1); // no self-approved license created

        $this->app['env'] = 'testing';
    }

public function test_service_usd_and_price_label_derive_from_configured_rate(): void
    {
        $service = Service::create([
            'title' => 'EDR Agent',
            'slug' => 'edr-agent-usd',
            'category' => 'Blue Team',
            'description' => 'Test',
            'icon' => '🛡️',
            'price' => 80000,
            'is_available' => true,
        ]);

        // Default rate is 10,000 SYP per USD => 80,000 SYP == $8.
        $this->assertSame(8.0, $service->usdPrice());
        $this->assertSame('80,000 SYP · $8/mo', $service->priceLabel());

        config(['cyberlogia.syp_per_usd' => 4000]);
        $this->assertSame(20.0, $service->usdPrice());

        // Free services fall back to a quote label.
        $free = Service::create([
            'title' => 'Free',
            'slug' => 'free-service',
            'category' => 'Blue Team',
            'description' => 'Test',
            'icon' => '🛡️',
            'price' => 0,
            'is_available' => true,
        ]);
        $this->assertSame('Custom Quote', $free->priceLabel());
    }

public function test_agent_api_endpoints_are_authenticated_and_rate_limited(): void
    {
        $service = Service::create([
            'title' => 'EDR Agent',
            'slug' => 'edr-agent-auth',
            'category' => 'Blue Team',
            'description' => 'Test',
            'icon' => '🛡️',
            'price' => 80000,
            'script_code' => "print('ok')",
        ]);

        // Unauthenticated callers must never reach the API surface.
        $this->getJson('/api/fetch-script?service_id='.$service->id.'&license_key=X')->assertUnauthorized();
        $this->postJson('/api/heartbeat', ['service_id' => $service->id])->assertUnauthorized();
        $this->postJson('/api/agent/token')->assertUnauthorized();
        $this->postJson('/api/v1/agents/poll', [])->assertUnauthorized();
        $this->postJson('/api/v1/agents/result', [])->assertUnauthorized();

        // A v1 agent key is required (agent.auth middleware).
        $this->postJson('/api/v1/agents/heartbeat', [])->assertUnauthorized();

        // Every API route must carry a throttle limiter.
        foreach (['api/fetch-script', 'api/heartbeat', 'api/v1/agents/register'] as $uri) {
            $route = collect(app('router')->getRoutes()->getRoutes())
                ->first(fn ($r) => $r->uri() === str_replace('api/', 'api/', $uri));
            if ($route) {
                $this->assertNotEmpty(
                    array_intersect(
                        ['throttle:api', 'throttle:agent-auth', 'throttle:agent-script'],
                        $route->gatherMiddleware()
                    ),
                    "Route [{$uri}] is missing rate limiting."
                );
            }
        }
    }

public function test_full_description_sanitizer_strips_tags_and_attributes(): void
    {
        $service = Service::create([
            'title' => 'Sanitizer',
            'slug' => 'sanitizer',
            'category' => 'Blue Team',
            'description' => 'Test',
            'icon' => '🛡️',
            'price' => 1000,
            'is_available' => true,
            'full_description' => '<h3 onclick="alert(1)">Title</h3>'
                .'<p>Safe <strong>text</strong></p>'
                .'<script>alert(2)</script>'
                .'<a href="javascript:alert(3)">bad link</a>',
        ]);

        $html = $service->fresh()->full_description;

        // Allowed structural tags survive.
        $this->assertStringContainsString('<h3>Title</h3>', $html);
        $this->assertStringContainsString('<strong>text</strong>', $html);

        // Dangerous markup is removed.
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('alert(1)', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function test_contact_form_validates_and_accepts_an_enquiry(): void
    {
        // The page renders with a real, wired form.
        $page = $this->get(route('contact'));
        $page->assertOk();
        $page->assertSee('Send an enquiry');
        $page->assertSee(route('contact.submit'), false);

        // Missing fields are rejected.
        $this->post(route('contact.submit'), [])
            ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);

        // Invalid email is rejected.
        $this->post(route('contact.submit'), [
            'name' => 'Jane',
            'email' => 'not-an-email',
            'subject' => 'Hello',
            'message' => 'Testing.',
        ])->assertSessionHasErrors('email');

        // A valid enquiry is accepted and redirected back with a flash message.
        $response = $this->post(route('contact.submit'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Enterprise licensing',
            'message' => 'We would like to deploy 25 EDR agents.',
        ]);

        $response->assertRedirect(route('contact'));
        $response->assertSessionHas('status');
        $this->assertGuest();
    }

    public function test_about_page_shows_cyberlogia_branding(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('CyberLogia')
            ->assertSee('Blue Team')
            ->assertSee('Red Team')
            ->assertSee('Cloud Security');
    }

    public function test_services_page_exposes_category_filters(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $page = $this->get(route('services'));
        $page->assertOk();

        // Branded headline replaces the old "The Arsenal" wording.
        $page->assertSee('Cybersecurity Services Matrix');
        $page->assertDontSee('The <span class="text-transparent', false);

        // Every category is offered as a filter control.
        $page->assertSee('All', false);
        $page->assertSee('activeCategory', false);   // Alpine filter state
        $page->assertSee('matches(', false);          // Alpine filter predicate
    }

    public function test_admin_subscriptions_report_renders_and_is_admin_only(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['email' => 'subscriber@example.com']);

        $service = Service::create([
            'title' => 'Automated EDR & Threat Hunting Agent',
            'slug' => 'report-edr',
            'category' => 'Blue Team',
            'description' => 'Test',
            'icon' => '🛡️',
            'price' => 80000,
            'is_automated' => true,
            'is_available' => true,
        ]);

        Payment::create([
            'user_id' => $customer->id,
            'service_id' => $service->id,
            'product_type' => 'service',
            'amount' => 80000,
            'status' => 'approved',
            'license_key' => 'CWK-REPORT-0001',
            'approved_at' => now()->subDays(20),
            'expires_at' => now()->addDays(10),
        ]);

        Payment::create([
            'user_id' => $customer->id,
            'service_id' => $service->id,
            'product_type' => 'service',
            'amount' => 80000,
            'status' => 'pending',
        ]);

        Payment::create([
            'user_id' => $customer->id,
            'service_id' => $service->id,
            'product_type' => 'service',
            'amount' => 80000,
            'status' => 'approved',
            'license_key' => 'CWK-REPORT-EXPIRED',
            'approved_at' => now()->subDays(60),
            'expires_at' => now()->subDays(5),
        ]);

        // Guests and non-admins are rejected.
        $this->get(route('admin.subscriptions.index'))->assertRedirect(route('login'));
        $this->actingAs($customer)->get(route('admin.subscriptions.index'))->assertForbidden();

        // Admin sees the report with licence keys and status badges.
        $response = $this->actingAs($admin)->get(route('admin.subscriptions.index'));
        $response->assertOk();
        $response->assertSee('Subscriptions');
        $response->assertSee('subscriber@example.com');
        $response->assertSee('CWK-REPORT-0001');
        $response->assertSee('Active');
        $response->assertSee('Pending Approval');
        $response->assertSee('Expired');

        // Sidebar link is present on the admin layout.
        $response->assertSee(route('admin.subscriptions.index'));
    }

    public function test_admin_subscriptions_report_filters_and_searches(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $a = User::factory()->create(['email' => 'alpha@example.com']);
        $b = User::factory()->create(['email' => 'beta@example.com']);

        $service = Service::create([
            'title' => 'FIM Agent', 'slug' => 'report-fim', 'category' => 'Blue Team',
            'description' => 'Test', 'icon' => '🔗', 'price' => 40000, 'is_available' => true,
        ]);

        Payment::create([
            'user_id' => $a->id, 'service_id' => $service->id, 'product_type' => 'service',
            'amount' => 40000, 'status' => 'approved', 'license_key' => 'CWK-ALPHA-KEY',
            'approved_at' => now(), 'expires_at' => now()->addDays(20),
        ]);
        Payment::create([
            'user_id' => $b->id, 'service_id' => $service->id, 'product_type' => 'service',
            'amount' => 40000, 'status' => 'pending',
        ]);

        // Filter by status = pending only returns the pending row.
        $pending = $this->actingAs($admin)->get(route('admin.subscriptions.index', ['status' => 'pending']));
        $pending->assertOk();
        $pending->assertSee('beta@example.com');
        $pending->assertDontSee('alpha@example.com');

        // Filter by status = active returns the approved row.
        $active = $this->actingAs($admin)->get(route('admin.subscriptions.index', ['status' => 'active']));
        $active->assertOk();
        $active->assertSee('alpha@example.com');
        $active->assertDontSee('beta@example.com');

        // Search by email.
        $search = $this->actingAs($admin)->get(route('admin.subscriptions.index', ['q' => 'alpha@']));
        $search->assertOk();
        $search->assertSee('alpha@example.com');
        $search->assertDontSee('beta@example.com');

        // Search by licence key.
        $licence = $this->actingAs($admin)->get(route('admin.subscriptions.index', ['q' => 'CWK-ALPHA-KEY']));
        $licence->assertOk();
        $licence->assertSee('alpha@example.com');
    }

    public function test_admin_can_approve_payment_and_issue_license(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $service = Service::create([
            'title' => 'VAPT',
            'slug' => 'vapt',
            'category' => 'Security',
            'description' => 'Test',
            'icon' => '🛡️',
            'price' => 50,
            'is_available' => true,
        ]);

        $payment = Payment::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'amount' => 50,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.payments.approve', $payment))
            ->assertRedirect();

        $payment->refresh();
        $this->assertSame('approved', $payment->status);
        $this->assertNotNull($payment->license_key);
    }

    public function test_user_can_upload_payment_receipt(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $service = Service::create([
            'title' => 'SOC',
            'slug' => 'soc',
            'category' => 'Security',
            'description' => 'Test',
            'icon' => '🛡️',
            'price' => 30,
            'is_available' => true,
        ]);

        $this->actingAs($user)
            ->post(route('payments.submit'), [
                'product_type' => 'service',
                'product_id' => $service->id,
                'account_name_number' => 'Test Account',
                'transaction_amount' => 30,
                'transaction_id_reference' => 'TXN-TEST-001',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'service_id' => $service->id,
            'status' => 'pending',
        ]);
    }

    public function test_sanctum_fetch_script_requires_approved_license(): void
    {
        $user = User::factory()->create();
        $service = Service::create([
            'title' => 'Scan',
            'slug' => 'scan',
            'category' => 'Security',
            'description' => 'Test',
            'icon' => '🛡️',
            'price' => 10,
            'script_code' => "print('ok')",
        ]);

        $token = $user->createToken('agent')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/fetch-script?service_id='.$service->id.'&license_key=INVALID')
            ->assertForbidden();

        $payment = Payment::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'amount' => 10,
            'status' => 'approved',
            'license_key' => 'CWK-TEST-KEY-001',
            'approved_at' => now(),
        ]);

        $this->withToken($token)
            ->getJson('/api/fetch-script?service_id='.$service->id.'&license_key='.$payment->license_key)
            ->assertOk()
            ->assertJsonPath('script_code', "print('ok')");
    }
}

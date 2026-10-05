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

        $script = $response->streamedContent();

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

        $this->actingAs($user)->get(
            route('my-tools.download-agent', [
                'service_id' => $service->id,
                'license_key' => 'NOT-A-REAL-LICENSE',
            ])
        )->assertForbidden();
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

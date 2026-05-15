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
            ->post(route('services.pay.store', $service), [
                'receipt' => UploadedFile::fake()->create('receipt.jpg', 100, 'image/jpeg'),
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

<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\Course;
use App\Models\Entitlement;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommandCenterAndAgentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_queue_command(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $agent = Agent::create([
            'user_id' => $admin->id,
            'agent_key' => 'agent-key-1',
            'device_name' => 'Client Laptop',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.command-center.commands.store'), [
            'agent_id' => $agent->id,
            'command_key' => 'run_health_check',
            'payload' => ['scope' => 'quick'],
            'ttl_seconds' => 300,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('agent_commands', [
            'agent_id' => $agent->id,
            'command_key' => 'run_health_check',
        ]);
    }

    public function test_non_admin_cannot_queue_command(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $agent = Agent::create([
            'user_id' => $user->id,
            'agent_key' => 'agent-key-2',
            'device_name' => 'Client Laptop',
        ]);

        $response = $this->actingAs($user)->post(route('admin.command-center.commands.store'), [
            'agent_id' => $agent->id,
            'command_key' => 'run_health_check',
        ]);

        $response->assertForbidden();
    }

    public function test_agent_can_register_poll_and_submit_result(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $registerResponse = $this->postJson('/api/v1/agent/register', [
            'user_id' => $admin->id,
            'agent_key' => 'agent-flow-key',
            'device_name' => 'Endpoint-A',
            'os_type' => 'windows',
            'agent_version' => '1.0.0',
        ])->assertCreated();

        $apiToken = $registerResponse->json('api_token');
        $agent = Agent::where('agent_key', 'agent-flow-key')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.command-center.commands.store'), [
            'agent_id' => $agent->id,
            'command_key' => 'collect_inventory',
        ])->assertRedirect();

        $pollResponse = $this->withHeaders([
            'Authorization' => 'Bearer '.$apiToken,
            'X-Agent-Key' => 'agent-flow-key',
        ])->postJson('/api/v1/agent/poll', []);

        $pollResponse->assertOk()->assertJsonPath('command.key', 'collect_inventory');

        $this->withHeaders([
            'Authorization' => 'Bearer '.$apiToken,
            'X-Agent-Key' => 'agent-flow-key',
        ])->postJson('/api/v1/agent/result', [
            'command_uuid' => $pollResponse->json('command.uuid'),
            'nonce' => 'result-nonce-1',
            'result_status' => 'succeeded',
            'stdout' => 'ok',
        ])->assertOk();

        $this->assertDatabaseHas('agent_command_results', [
            'result_status' => 'succeeded',
        ]);
    }

    public function test_entitlement_gates_academy_endpoint(): void
    {
        $user = User::factory()->create();
        $course = Course::create([
            'title' => 'Locked Course',
            'slug' => 'locked-course',
            'description' => 'desc',
            'level' => 'Beginner',
            'is_active' => true,
            'requires_purchase' => true,
        ]);

        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'M1',
            'order_no' => 1,
        ]);

        $this->actingAs($user)->get(route('academy.course.show', $course))->assertForbidden();

        Entitlement::create([
            'user_id' => $user->id,
            'entitlement_type' => 'course',
            'entitlement_id' => $course->id,
            'is_active' => true,
        ]);

        $this->actingAs($user)->get(route('academy.course.show', $course))->assertOk();
        $this->actingAs($user)->get(route('academy.module.show', $module))->assertOk();
    }
}

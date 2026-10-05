<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Registration must NOT log the user in automatically.
        $this->assertGuest();

        // It must land on the login screen with a confirmation flash message.
        $response->assertRedirect(route('login', absolute: false));
        $response->assertSessionHas('status', 'Registration successful! Please log in.');

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_registration_screen_links_to_login(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee(route('login', absolute: false));
    }
}

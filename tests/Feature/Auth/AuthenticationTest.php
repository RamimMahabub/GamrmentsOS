<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_owner_can_authenticate_using_admin_login_id(): void
    {
        $user = User::factory()->create(['name' => 'admin', 'password' => 'admin123']);

        $response = $this->post('/login', [
            'email' => 'admin',
            'password' => 'admin123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_admin_login_id_requires_the_correct_password(): void
    {
        User::factory()->create(['name' => 'admin', 'password' => 'admin123']);

        $response = $this->post('/login', [
            'email' => 'admin',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_unknown_login_id_cannot_authenticate(): void
    {
        User::factory()->create(['name' => 'admin', 'password' => 'admin123']);

        $response = $this->post('/login', [
            'email' => 'unknown',
            'password' => 'admin123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}

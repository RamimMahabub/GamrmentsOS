<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_is_disabled_for_the_single_owner_workspace(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', ['name' => 'Someone', 'email' => 'someone@example.com', 'password' => 'password', 'password_confirmation' => 'password'])->assertNotFound();
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_profile_can_be_viewed_and_updated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->get('/profile')->assertOk();
        $this->patch('/profile', ['name' => 'Owner', 'email' => 'owner@example.com'])->assertSessionHasNoErrors()->assertRedirect('/profile');
        $this->assertSame('Owner', $user->fresh()->name);
        $this->assertSame('owner@example.com', $user->fresh()->email);
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertSame($user->id, $user->fresh()->updated_by);
    }

    public function test_owner_account_cannot_be_deleted_from_the_workspace(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->delete('/profile', ['password' => 'password'])->assertStatus(405);
        $this->assertNotNull($user->fresh());
    }

    public function test_changing_only_the_name_preserves_email_verification(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->patch('/profile', ['name' => 'Owner', 'email' => $user->email])->assertSessionHasNoErrors();
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}

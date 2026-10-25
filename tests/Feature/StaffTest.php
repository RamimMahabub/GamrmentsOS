<?php

namespace Tests\Feature;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_lifecycle_retains_history_and_supports_search_and_productivity(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post(route('staffs.store'), ['name' => 'Rahim', 'phone' => '01712345678', 'role' => 'cutting_man', 'is_active' => true])->assertSessionHasNoErrors();
        $staff = Staff::firstOrFail();
        $this->get(route('staffs.index', ['q' => 'Rahim']))->assertOk()->assertSee('Rahim');
        $this->get(route('staffs.show', $staff))->assertOk()->assertSee('Grand total: 0.000');
        $this->put(route('staffs.update', $staff), ['name' => 'Karim', 'phone' => '01712345678', 'role' => 'sewing_man', 'is_active' => true])->assertSessionHasNoErrors();
        $this->delete(route('staffs.destroy', $staff))->assertRedirect(route('staffs.index'));
        $this->assertDatabaseHas('staffs', ['id' => $staff->id, 'name' => 'Karim', 'is_active' => false]);
    }

    public function test_required_fields_and_authentication(): void
    {
        $this->get(route('staffs.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->post(route('staffs.store'), [])->assertSessionHasErrors(['name', 'phone', 'role', 'is_active']);
    }
}

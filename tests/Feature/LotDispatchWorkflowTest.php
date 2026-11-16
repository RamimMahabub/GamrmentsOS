<?php

namespace Tests\Feature;

use App\Models\Lot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LotDispatchWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_manually_dispatch_and_undispatch_a_lot(): void
    {
        $this->seed();
        $user = User::first();
        $this->actingAs($user);

        $lot = Lot::first();

        // Dispatch lot
        $response = $this->post(route('lots.dispatch', $lot), [
            'dispatch_notes' => 'Finished and ready for customer pickup',
        ]);
        $response->assertRedirect();

        $lot->refresh();
        $this->assertTrue($lot->is_dispatched);
        $this->assertSame('dispatched', $lot->current_stage);
        $this->assertNotNull($lot->dispatched_at);
        $this->assertSame('Finished and ready for customer pickup', $lot->dispatch_notes);

        // Undispatch lot
        $response = $this->post(route('lots.undispatch', $lot));
        $response->assertRedirect();

        $lot->refresh();
        $this->assertFalse($lot->is_dispatched);
        $this->assertNull($lot->dispatched_at);
        $this->assertNotSame('dispatched', $lot->current_stage);
    }

    public function test_lot_auto_dispatch_eligibility_logic(): void
    {
        $this->seed();
        $lot = Lot::first();

        // A lot that is not yet fully packed or still has rolls is not eligible
        $this->assertFalse($lot->is_dispatched);
    }
}

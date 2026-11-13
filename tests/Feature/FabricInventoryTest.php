<?php

namespace Tests\Feature;

use App\Models\Lot;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FabricInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_fabric_inventory_page_renders_with_dyeing_style_overview(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::first();

        $response = $this->actingAs($user)->get(route('fabric.index'));

        $response->assertOk()
            ->assertSee('Fabric &amp; Rolls', false)
            ->assertSee('Lot Identity')
            ->assertSee('Dyeing House')
            ->assertSee('Available Fabric')
            ->assertSee('View Details');
    }

    public function test_fabric_detail_page_renders_itemized_rolls_and_balances(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::first();
        $lot = Lot::whereHas('dyeingJobs.colorBlocks.rows', fn ($q) => $q->whereNotNull('returned_fabric_kg'))->firstOrFail();

        $response = $this->actingAs($user)->get(route('fabric.show', $lot));

        $response->assertOk()
            ->assertSee($lot->lot_number)
            ->assertSee('Itemized Rolls', false)
            ->assertSee('Fabric Balance', false);
    }
}

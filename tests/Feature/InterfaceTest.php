<?php

namespace Tests\Feature;

use App\Livewire\CompanyPicker;
use App\Models\Company;
use App\Models\DyeingJob;
use App\Models\KnittingJob;
use App\Models\Lot;
use App\Models\User;
use App\Models\Yarn;
use App\Modules\Dyeing\FabricAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InterfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_phase_one_pages_render_with_the_reference_demo(): void
    {
        $this->seed();
        $this->actingAs(User::first());
        $yarn = Yarn::first();
        $company = Company::first();
        $knitting = KnittingJob::first();
        $dyeing = DyeingJob::first();
        $lot = Lot::first();
        foreach (['/dashboard', '/yarns', '/yarns/create', '/yarns/'.$yarn->id, '/yarns/'.$yarn->id.'/edit', '/companies', '/companies/create', '/companies/'.$company->id,
            '/companies/'.$company->id.'/edit', '/knitting-jobs', '/knitting-jobs/create', '/knitting-jobs/'.$knitting->id, '/knitting-jobs/'.$knitting->id.'/edit',
            '/dyeing-jobs', '/dyeing-jobs/create', '/dyeing-jobs/'.$dyeing->id, '/dyeing-jobs/'.$dyeing->id.'/edit', '/lots/'.$lot->id, '/settings', '/profile'] as $path) {
            $this->get($path)->assertOk();
        }
        $this->get('/dashboard')->assertSee('CO-031026')->assertSeeInOrder(['Cutting (Body)', 'Cutting (Neck)', 'Cutting (Rib)', 'Staffs', 'Settings'])->assertDontSee('Sewing')->assertDontSee('Packing');
        $this->get('/lots/'.$lot->id)->assertSee('Navy')->assertSee('78.800')->assertSee('13.133')->assertSee('4.00%')->assertSee('Weight through the stages');
        $availableLots = Lot::where('id', '!=', $lot->id)->orderBy('id')->pluck('id')->all();
        $this->assertSame($availableLots, app(FabricAvailability::class)->selectable()->orderBy('id')->pluck('id')->all());
        $this->assertCount(3, app(FabricAvailability::class)->selectable($lot->id)->pluck('id')->all());
    }

    public function test_inline_company_creation_keeps_the_assignment_flow(): void
    {
        $this->actingAs(User::factory()->create());
        Livewire::test(CompanyPicker::class, ['type' => 'knitting'])->set('open', true)
            ->set('name', 'Inline Knit')->set('address', 'Dhaka')
            ->set('contacts.manager.name', 'Rahim')->set('contacts.manager.phone', '+880 1700-000001')
            ->set('contacts.operator.name', 'Kamal')->set('contacts.operator.phone', '+880 1700-000002')
            ->call('createCompany')->assertHasNoErrors()->assertSet('open', false)->assertSet('selected', (string) Company::first()->id);
        $this->assertDatabaseHas('company_contacts', ['role' => 'operator', 'phone' => '+8801700000002']);
    }

    public function test_inline_company_validation_requires_the_dyeing_master(): void
    {
        $this->actingAs(User::factory()->create());
        Livewire::test(CompanyPicker::class, ['type' => 'dyeing'])->set('name', 'Inline Dye')->set('address', 'Dhaka')
            ->call('createCompany')->assertHasErrors(['contacts.dyeing_master.name', 'contacts.dyeing_master.phone']);
        $this->assertDatabaseCount('companies', 0);
    }

    public function test_preview_and_calculation_endpoints_use_authoritative_services(): void
    {
        $this->actingAs(User::factory()->create());
        $yarn = Yarn::factory()->create(['name' => 'Sudan']);
        $this->get('/api/lots/preview?yarn_id='.$yarn->id.'&date=2026-05-10')->assertOk()->assertJson(['lot_number' => 'SU-100526']);
        $this->postJson('/api/calculations', ['input_kg' => '226.796', 'waste_percent' => '1', 'returned_kg' => '224.528'])->assertOk()->assertJson(['output_kg' => '224.528', 'dyeing_waste_percent' => '1.00']);
        $this->getJson('/api/lots/preview?yarn_id='.$yarn->id.'&date=wrong')->assertUnprocessable();
        $this->postJson('/api/calculations', ['input_kg' => '2,500.000', 'waste_percent' => '1', 'returned_kg' => '2,400.000'])->assertOk()->assertJson(['output_kg' => '2475.000', 'dyeing_waste_percent' => '4.00']);
    }

    public function test_seed_is_idempotent_and_all_transaction_audits_are_present(): void
    {
        $this->seed();
        $this->seed();
        $this->assertDatabaseCount('lots', 3);
        $this->assertDatabaseCount('dyeing_jobs', 2);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('yarn_stock_movements', 7);
        $this->assertDatabaseHas('activity_logs', ['subject_type' => DyeingJob::class, 'event' => 'created']);
    }
}

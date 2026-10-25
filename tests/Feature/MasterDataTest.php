<?php

namespace Tests\Feature;

use App\Models\Color;
use App\Models\Company;
use App\Models\DyeingJob;
use App\Models\User;
use App\Models\Yarn;
use App\Modules\Dyeing\DyedFabricBalance;
use App\Modules\Dyeing\FabricAvailability;
use App\Modules\Knitting\KnittingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ManufacturingFixtures;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use ManufacturingFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    private function companyData(): array
    {
        return ['name' => 'Both Services', 'address' => 'Dhaka', 'is_knitting' => '1', 'is_dyeing' => '1', 'is_active' => '1',
            'contacts' => ['manager' => ['name' => 'Manager', 'phone' => '+880 1700-000001'], 'operator' => ['name' => 'Operator', 'phone' => '+880 1700-000002'], 'dyeing_master' => ['name' => 'Master', 'phone' => '+880 1700-000003']]];
    }

    public function test_company_contacts_types_phone_normalization_and_case_insensitive_uniqueness(): void
    {
        $data = $this->companyData();
        $this->post('/companies', $data)->assertSessionHasNoErrors();
        $company = Company::first();
        $this->assertCount(3, $company->contacts);
        $this->assertSame('+8801700000001', $company->contacts->firstWhere('role', 'manager')->phone);
        $data['name'] = 'BOTH SERVICES';
        $this->post('/companies', $data)->assertSessionHasErrors('name');
        $data = $this->companyData();
        $data['contacts']['operator']['phone'] = '';
        $this->put(route('companies.update', $company), $data)->assertSessionHasErrors('contacts.operator.phone');
        $data = $this->companyData();
        $data['is_knitting'] = '0';
        $data['is_dyeing'] = '0';
        $this->post('/companies', $data)->assertSessionHasErrors('is_knitting');
    }

    public function test_referenced_company_can_be_deactivated_but_not_deleted_or_retyped(): void
    {
        $data = $this->companyData();
        $this->post('/companies', $data)->assertSessionHasNoErrors();
        $company = Company::first();
        $yarn = Yarn::factory()->withStock()->create();
        app(KnittingService::class)->save($this->knittingData($yarn, $company, '100.000', [32]));
        $this->delete(route('companies.destroy', $company))->assertSessionHasErrors('company');
        $data['is_knitting'] = '0';
        $this->put(route('companies.update', $company), $data)->assertSessionHasErrors('company');
        $data = $this->companyData();
        $data['is_active'] = '0';
        $this->put(route('companies.update', $company), $data)->assertSessionHasNoErrors();
        $this->post('/knitting-jobs', $this->knittingData($yarn, $company, '100.000', [32]))->assertSessionHasErrors('company_id');
        $this->assertFalse($company->fresh()->is_active);
    }

    public function test_validation_rejects_wrong_types_duplicate_dias_and_bad_dates(): void
    {
        $company = Company::factory()->create(['is_knitting' => false]);
        $yarn = Yarn::factory()->withStock()->create();
        $data = $this->knittingData($yarn, $company);
        $this->post('/knitting-jobs', $data)->assertSessionHasErrors('company_id');
        $data['rows'][1]['dia'] = 32;
        $this->post('/knitting-jobs', $data)->assertSessionHasErrors('rows.0.dia');
        $data = $this->knittingData($yarn, $company);
        $data['delivery_date'] = '2026-05-01';
        $this->post('/knitting-jobs', $data)->assertSessionHasErrors('delivery_date');
        $data = $this->knittingData($yarn, $company);
        $data['rows'][0]['quantities'][$yarn->id] = '-1';
        $this->post('/knitting-jobs', $data)->assertSessionHasErrors('rows.0.quantities.'.$yarn->id);
    }

    public function test_stock_ledger_cannot_be_edited(): void
    {
        $yarn = Yarn::factory()->withStock()->create();
        $this->expectException(\LogicException::class);
        $yarn->movements()->first()->update(['quantity_kg' => '2000.000']);
    }

    public function test_settings_validate_presets_and_color_rename_merge(): void
    {
        $this->put('/settings', ['default_weight_unit' => 'kg', 'dia_presets' => [32, 34], 'currency_label' => 'BDT'])->assertSessionHasNoErrors();
        $this->put('/settings', ['default_weight_unit' => 'kg', 'dia_presets' => [32, 32], 'currency_label' => 'BDT'])->assertSessionHasErrors('dia_presets.0');
        $source = Color::factory()->create(['name' => 'Golden', 'name_key' => 'golden']);
        $target = Color::factory()->create(['name' => 'Olive', 'name_key' => 'olive']);
        $this->put(route('settings.color', $source), ['name' => 'Gold', 'hex' => '#DAA520'])->assertSessionHasNoErrors();
        $this->assertSame('gold', $source->fresh()->name_key);
        $this->put(route('settings.color', $source), ['name' => 'Gold', 'target_id' => $target->id])->assertSessionHasNoErrors();
        $this->assertSoftDeleted($source);
    }

    public function test_unused_master_records_can_be_soft_deleted(): void
    {
        $company = Company::factory()->create();
        $this->delete(route('companies.destroy', $company))->assertSessionHasNoErrors();
        $this->assertSoftDeleted($company);
        $yarn = Yarn::factory()->create();
        $this->delete(route('yarns.destroy', $yarn))->assertSessionHasNoErrors();
        $this->assertSoftDeleted($yarn);
    }

    public function test_color_merge_consolidates_existing_job_rows_without_changing_fabric_balance(): void
    {
        $yarn = Yarn::factory()->withStock()->create();
        $company = Company::factory()->create();
        $job = app(KnittingService::class)->save($this->knittingData($yarn, $company, '200.000', [32]));
        $this->post('/dyeing-jobs', $this->dyeingData($job->lot_id, $company, '50.000', 2, '45.000', [32]))->assertSessionHasNoErrors();
        $gold = Color::where('name_key', 'golden')->first();
        $olive = Color::where('name_key', 'olive')->first();
        $this->put(route('settings.color', $gold), ['name' => 'Golden', 'target_id' => $olive->id])->assertSessionHasNoErrors();
        $dyeing = DyeingJob::first();
        $this->assertCount(1, $dyeing->colorBlocks);
        $row = $dyeing->colorBlocks->first()->rows->first();
        $this->assertSame('100.000', $row->given_fabric_kg);
        $this->assertSame('90.000', $row->returned_fabric_kg);
        $this->assertSame('10.00', $row->waste_percent);
        $this->assertSame('100.000', app(FabricAvailability::class)->forLot($job->lot->fresh())[0]['available_kg']);
        $this->assertSame('90.000', app(DyedFabricBalance::class)->forLot($job->lot)[0]['quantity_kg']);
    }

    public function test_malformed_nested_inputs_are_validation_errors(): void
    {
        $this->postJson('/knitting-jobs', ['yarn_ids' => 'invalid', 'rows' => 'invalid'])->assertUnprocessable();
        $this->postJson('/knitting-jobs',['yarn_ids' => [1], 'rows' => ['invalid']])->assertUnprocessable();
        $this->postJson('/yarns',['price_per_kg' => json_decode('1.1')])->assertUnprocessable();
    }
}

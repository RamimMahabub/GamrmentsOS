<?php

namespace Database\Seeders;

use App\Models\Lot;
use App\Models\User;
use App\Modules\Companies\CompanyService;
use App\Modules\Dyeing\DyeingService;
use App\Modules\Dyeing\FabricAvailability;
use App\Modules\Knitting\KnittingService;
use App\Modules\Yarn\UnitConverter;
use App\Modules\Yarn\YarnService;
use App\Support\Decimal as D;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Sample workflow is available only in local and testing environments.');
        }
        if (Lot::withTrashed()->exists()) {
            return;
        }
        DB::transaction(function (): void {
            $owner = User::first() ?? User::create(['name' => 'Factory Owner', 'email' => 'owner@garmentos.test', 'password' => Hash::make('ChangeMe2026!'), 'email_verified_at' => now()]);
            $previous = Auth::user();
            Auth::login($owner);
            try {
                $knitter = app(CompanyService::class)->save(['name' => 'Tongi Circular Knitting', 'address' => 'BSCIC Industrial Area, Tongi, Gazipur', 'is_knitting' => true, 'is_dyeing' => false, 'is_active' => true,
                    'contacts' => ['manager' => ['name' => 'Arif Hossain', 'phone' => '+880 1700-000011'], 'operator' => ['name' => 'Masud Rana', 'phone' => '+880 1700-000012']],
                    'notes' => 'Fictional sample partner for the local workflow.']);
                $dyer = app(CompanyService::class)->save(['name' => 'Konabari Fabric Finishing', 'address' => 'Konabari Industrial Area, Gazipur', 'is_knitting' => false, 'is_dyeing' => true, 'is_active' => true,
                    'contacts' => ['dyeing_master' => ['name' => 'Sajjad Karim', 'phone' => '+880 1700-000013']],
                    'notes' => 'Fictional sample partner for the local workflow.']);
                $cotton = app(YarnService::class)->save(['name' => 'Combed Cotton', 'yarn_type' => '30/1 Single', 'country_of_origin' => 'Bangladesh', 'price_per_kg' => '385.000000', 'price_unit' => 'kg',
                    'bag_size_kg' => '50.00000000', 'bag_size_unit' => 'kg', 'initial_kg' => '2000.000', 'movement_date' => '2026-10-01']);
                app(YarnService::class)->addStock($cotton, ['quantity_kg' => '400.000', 'movement_date' => '2026-10-08', 'price_per_kg' => '390.000000', 'note' => 'Supplementary cotton purchase, 8 bags.']);
                $melange = app(YarnService::class)->save(['name' => 'Grey Melange', 'yarn_type' => '26/1 Single, 90% cotton 10% viscose', 'country_of_origin' => 'India', 'price_per_kg' => '420.000000', 'price_unit' => 'kg',
                    'bag_size_kg' => '45.35923700', 'bag_size_unit' => 'lb', 'initial_kg' => '800.000', 'movement_date' => '2026-10-02']);
                app(YarnService::class)->save(['name' => 'Carded Cotton', 'yarn_type' => '24/1 Single', 'country_of_origin' => 'Bangladesh', 'price_per_kg' => '345.000000', 'price_unit' => 'kg',
                    'bag_size_kg' => '50.00000000', 'bag_size_unit' => 'kg', 'initial_kg' => '600.000', 'movement_date' => '2026-10-12']);
                $complete = app(KnittingService::class)->save(['company_id' => $knitter->id, 'yarn_ids' => [$cotton->id], 'entry_date' => '2026-10-03', 'delivery_date' => '2026-10-07', 'input_unit' => 'kg',
                    'notes' => 'Single jersey, 160 GSM. 500 kg yarn issued; 7.500 kg knitting loss. All returned dyed fabric is itemized into rolls.',
                    'rows' => array_map(fn (int $dia, string $kg): array => ['dia' => $dia, 'waste_percent' => '1.50', 'quantities' => [$cotton->id => $kg]], [28, 30, 32, 34, 36, 40], ['80.000', '90.000', '100.000', '80.000', '70.000', '80.000'])]);
                $this->dye($complete->lot_id, $dyer->id, ['Navy', 'Black', 'Olive', 'Burgundy', 'Bottle Green', 'Ivory'], false, '2026-10-08', '2026-10-14');
                $partial = app(KnittingService::class)->save(['company_id' => $knitter->id, 'yarn_ids' => [$melange->id], 'entry_date' => '2026-10-09', 'delivery_date' => '2026-10-12', 'input_unit' => 'kg',
                    'notes' => 'Melange jersey, 180 GSM. Half of each DIA sent to dyeing; the other half remains in grey fabric stock.',
                    'rows' => array_map(fn (int $dia): array => ['dia' => $dia, 'waste_percent' => '2.00', 'quantities' => [$melange->id => '50.000']], [28, 30, 32, 34, 36, 40])]);
                $this->dye($partial->lot_id, $dyer->id, ['Navy', 'Black', 'Olive', 'Burgundy', 'Bottle Green', 'Ivory'], true, '2026-10-13', null);
                app(KnittingService::class)->save(['company_id' => $knitter->id, 'yarn_ids' => [$cotton->id], 'entry_date' => '2026-10-15', 'delivery_date' => '2026-10-17', 'input_unit' => 'kg',
                    'notes' => 'Repeat single jersey order, 160 GSM. Knitting completed; 197.000 kg awaiting color allocation and dyeing.',
                    'rows' => array_map(fn (int $dia, string $kg): array => ['dia' => $dia, 'waste_percent' => '1.50', 'quantities' => [$cotton->id => $kg]], [28, 30, 32, 34, 36, 40], ['40.000', '35.000', '30.000', '35.000', '30.000', '30.000'])]);
            } finally {
                if ($previous) {
                    Auth::login($previous);
                } else {
                    Auth::logout();
                }
            }
        }, 5);
    }

    private function dye(int $lotId, int $companyId, array $colors, bool $partial, string $entryDate, ?string $returnDate): void
    {
        $fabric = app(FabricAvailability::class)->forLot(Lot::findOrFail($lotId));
        $blocks = [];
        foreach ($colors as $index => $color) {
            $rows = [];
            foreach ($fabric as $diaIndex => $row) {
                $allocation = $partial ? D::scale(D::of($row['available_kg'])->multipliedBy('0.5')) : $row['available_kg'];
                $given = app(FabricAvailability::class)->split($allocation, count($colors))[$index];
                $rolls = [];
                if (! $partial || $index < 3) {
                    $returned = app(FabricAvailability::class)->split(D::scale(D::of($allocation)->multipliedBy('0.96')), count($colors))[$index];
                    $pounds = (string) (4 + $diaIndex % 3);
                    $remainder = D::of($returned)->minus(UnitConverter::toKg($pounds, 'lb'));
                    $rollCount = 2 + ($index + $diaIndex) % 2;
                    $parts = app(FabricAvailability::class)->split(D::scale($remainder), $rollCount - 1);
                    $weights = array_merge([[$pounds, 'lb']], array_map(fn (string $weight): array => [$weight, 'kg'], $parts));
                    foreach ($weights as $number => [$weight, $unit]) {
                        $rolls[] = ['number' => 'L'.$lotId.'-'.strtoupper(substr($color, 0, 3)).'-'.$row['dia'].'-'.($number + 1), 'weight' => $weight, 'unit' => $unit];
                    }
                }
                $rows[] = ['dia' => $row['dia'], 'given_fabric_kg' => $given, 'returned_fabric_kg' => null, 'rolls' => $rolls];
            }
            $blocks[] = ['color' => $color, 'rows' => $rows];
        }
        app(DyeingService::class)->save(['company_id' => $companyId, 'lot_id' => $lotId, 'entry_date' => $entryDate, 'return_date' => $returnDate,
            'notes' => $partial ? 'Navy, Black and Olive returned in 45 weighed rolls across six DIAs; three other colors remain at the dye house. Grey stock remains available.' : 'Six colors across six DIAs returned in 90 weighed rolls, 2 or 3 per color per DIA. Finished fabric ready for cutting; dyeing loss is 4%.', 'blocks' => $blocks]);
    }
}

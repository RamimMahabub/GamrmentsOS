<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            foreach (['default_weight_unit' => 'lb', 'dia_presets' => [32, 34, 36, 40], 'currency_label' => 'BDT'] as $key => $value) {
                Setting::firstOrCreate(['key' => $key], ['value' => $value]);
            }
        });
        if (app()->environment(['local', 'testing'])) {
            $this->call(DemoSeeder::class);
        }
    }
}

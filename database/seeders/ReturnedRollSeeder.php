<?php

namespace Database\Seeders;

use App\Models\DyeingJob;
use App\Models\Lot;
use App\Support\ReturnedRolls;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReturnedRollSeeder extends Seeder
{
    public function run(): void
    {
        DyeingJob::orderBy('id')->each(function (DyeingJob $job): void {
            DB::transaction(function () use ($job): void {
                Lot::whereKey($job->lot_id)->lockForUpdate()->firstOrFail();
                app(ReturnedRolls::class)->sync($job);
            });
        });
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CuttingJobRollSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CuttingJobSeeder::class);
    }
}

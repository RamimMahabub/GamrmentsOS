<?php

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        Staff::firstOrCreate(['phone' => '01712345678'], ['name' => 'Sample cutting man', 'role' => 'cutting_man', 'is_active' => true]);
    }
}

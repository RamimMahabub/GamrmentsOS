<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function columns(Blueprint $table, bool $nullable): void
    {
        foreach (['dozens' => 3, 'weight_per_dozen' => 6, 'weight_per_dozen_kg' => 8, 'cut_weight_kg' => 3, 'cutting_scrap_kg' => 3, 'waste_kg' => 3, 'waste_percent' => 2] as $column => $scale) {
            $table->decimal($column, $column === 'waste_percent' ? 7 : 18, $scale)->nullable($nullable)->change();
        }
    }

    public function up(): void
    {
        Schema::table('cutting_job_rolls', fn (Blueprint $table) => $this->columns($table, true));
    }

    public function down(): void
    {
        if (DB::table('cutting_job_rolls')->whereNull('cut_weight_kg')->exists()) {
            throw new RuntimeException('Complete pending cutting results before rolling back this migration.');
        }
        Schema::table('cutting_job_rolls', fn (Blueprint $table) => $this->columns($table, false));
    }
};

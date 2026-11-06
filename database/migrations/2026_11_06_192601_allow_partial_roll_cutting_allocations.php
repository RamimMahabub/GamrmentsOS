<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cutting_job_rolls', function (Blueprint $table): void {
            $table->dropUnique('cutting_rows_exclusive_roll_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cutting_job_rolls', function (Blueprint $table): void {
            $table->unique(['roll_id', 'exclusive_cutting_type'], 'cutting_rows_exclusive_roll_type_unique');
        });
    }
};

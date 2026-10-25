<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cutting_job_rolls', function (Blueprint $table): void {
            $table->string('exclusive_cutting_type', 8)->nullable()
                ->virtualAs("case when cutting_type = 'neck' then null else cutting_type end");
            $table->unique(['roll_id', 'exclusive_cutting_type'], 'cutting_rows_exclusive_roll_type_unique');
            $table->unique(['cutting_job_id', 'roll_id'], 'cutting_rows_job_roll_unique');
            $table->index(['roll_id', 'cutting_type'], 'cutting_rows_roll_type_index');
            $table->dropUnique(['roll_id', 'cutting_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('cutting_job_rolls')->select('roll_id', 'cutting_type')->groupBy('roll_id', 'cutting_type')->havingRaw('count(*) > 1')->exists()) {
            throw new RuntimeException('Combine partial neck assignments before rolling back this migration.');
        }

        Schema::table('cutting_job_rolls', function (Blueprint $table): void {
            $table->unique(['roll_id', 'cutting_type']);
            $table->dropUnique('cutting_rows_exclusive_roll_type_unique');
            $table->dropUnique('cutting_rows_job_roll_unique');
            $table->dropIndex('cutting_rows_roll_type_index');
            $table->dropColumn('exclusive_cutting_type');
        });
    }
};

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
        Schema::table('companies', function (Blueprint $table): void {
            $table->decimal('default_knitting_rate_per_kg', 18, 6)->nullable()->after('is_dyeing');
            $table->decimal('default_dyeing_rate_per_kg', 18, 6)->nullable()->after('default_knitting_rate_per_kg');
        });

        Schema::table('knitting_jobs', function (Blueprint $table): void {
            $table->decimal('rate_per_kg', 18, 6)->nullable()->after('input_unit');
        });

        Schema::table('dyeing_jobs', function (Blueprint $table): void {
            $table->decimal('rate_per_kg', 18, 6)->nullable()->after('return_date');
        });

        Schema::table('cutting_jobs', function (Blueprint $table): void {
            $table->decimal('rate_per_dozen', 18, 6)->nullable()->after('notes');
        });

        Schema::table('sewing_jobs', function (Blueprint $table): void {
            $table->decimal('rate_per_dozen', 18, 6)->nullable()->after('notes');
        });

        Schema::table('lots', function (Blueprint $table): void {
            $table->decimal('carrying_cost_per_dozen', 18, 6)->nullable()->after('current_stage');
            $table->decimal('thread_cost_per_dozen', 18, 6)->nullable()->after('carrying_cost_per_dozen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table): void {
            $table->dropColumn(['carrying_cost_per_dozen', 'thread_cost_per_dozen']);
        });

        Schema::table('sewing_jobs', function (Blueprint $table): void {
            $table->dropColumn('rate_per_dozen');
        });

        Schema::table('cutting_jobs', function (Blueprint $table): void {
            $table->dropColumn('rate_per_dozen');
        });

        Schema::table('dyeing_jobs', function (Blueprint $table): void {
            $table->dropColumn('rate_per_kg');
        });

        Schema::table('knitting_jobs', function (Blueprint $table): void {
            $table->dropColumn('rate_per_kg');
        });

        Schema::table('companies', function (Blueprint $table): void {
            $table->dropColumn(['default_knitting_rate_per_kg', 'default_dyeing_rate_per_kg']);
        });
    }
};

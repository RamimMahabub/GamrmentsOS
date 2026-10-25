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
            $table->boolean('weight_is_automatic')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cutting_job_rolls', function (Blueprint $table): void {
            $table->dropColumn('weight_is_automatic');
        });
    }
};

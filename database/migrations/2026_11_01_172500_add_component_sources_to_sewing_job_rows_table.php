<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sewing_job_rows', function (Blueprint $table): void {
            $table->foreignId('source_neck_row_id')->nullable()->after('source_body_row_id')->constrained('cutting_job_rolls')->restrictOnDelete();
            $table->foreignId('source_rib_row_id')->nullable()->after('source_neck_row_id')->constrained('cutting_job_rolls')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sewing_job_rows', function (Blueprint $table): void {
            $table->dropForeign(['source_neck_row_id']);
            $table->dropForeign(['source_rib_row_id']);
            $table->dropColumn(['source_neck_row_id', 'source_rib_row_id']);
        });
    }
};

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
        Schema::create('sewing_job_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sewing_job_id')->constrained()->restrictOnDelete();
            $table->foreignId('source_body_row_id')->constrained('cutting_job_rolls')->restrictOnDelete();
            $table->foreignId('lot_id')->constrained()->restrictOnDelete();
            $table->string('color');
            $table->unsignedInteger('dia');
            foreach (['fold_dozens', 'lock_dozens', 'down_dozens', 'neck_join_dozens', 'neck_finishing_dozens'] as $column) {
                $table->decimal($column, 18, 3)->default('0');
            }
            $table->unsignedInteger('rejected_pieces')->default(0);
            $table->timestamps();
            $table->unique(['sewing_job_id', 'source_body_row_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sewing_job_rows');
    }
};

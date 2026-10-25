<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returned_rolls', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('dyeing_job_id')->constrained()->restrictOnDelete();
            $table->foreignId('dyeing_row_id')->constrained()->restrictOnDelete();
            $table->string('number');
            $table->string('number_key');
            $table->decimal('weight', 18, 3);
            $table->string('unit', 2);
            $table->decimal('weight_kg', 18, 3);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->unique(['dyeing_job_id', 'number_key']);
        });
        Schema::create('cutting_jobs', function (Blueprint $table): void {
            $table->id();
            $table->string('cutting_type', 8)->index();
            $table->date('cutting_date')->index();
            $table->foreignId('staff_id')->constrained('staffs')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        });
        Schema::create('cutting_job_rolls', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cutting_job_id')->constrained()->restrictOnDelete();
            $table->foreignId('roll_id')->constrained('returned_rolls')->restrictOnDelete();
            $table->string('cutting_type', 8);
            $table->foreignId('lot_id')->constrained()->restrictOnDelete();
            $table->string('color');
            $table->unsignedInteger('dia');
            foreach (['roll_weight_kg', 'dozens', 'cut_weight_kg', 'remaining_fabric', 'remaining_fabric_kg', 'cutting_scrap_kg', 'waste_kg'] as $column) {
                $definition = $table->decimal($column, 18, 3);
                if (in_array($column, ['remaining_fabric', 'remaining_fabric_kg'], true)) {
                    $definition->default('0');
                }
            }
            $table->decimal('weight_per_dozen', 18, 6);
            $table->decimal('weight_per_dozen_kg', 18, 8);
            $table->string('weight_unit', 2);
            $table->string('remaining_fabric_unit', 2);
            $table->decimal('waste_percent', 7, 2);
            $table->timestamps();
            $table->unique(['roll_id', 'cutting_type']);
        });
        DB::table('dyeing_rows')->whereNull('deleted_at')->orderBy('id')->each(function (object $row): void {
            $block = DB::table('dyeing_color_blocks')->where('id', $row->color_block_id)->whereNull('deleted_at')->first();
            if (! $block || ! DB::table('dyeing_jobs')->where('id', $block->dyeing_job_id)->whereNull('deleted_at')->exists() || $row->returned_fabric_kg === null) {
                return;
            }
            foreach (json_decode($row->rolls ?? '[]', true) ?? [] as $roll) {
                DB::table('returned_rolls')->insert(['dyeing_job_id' => $block->dyeing_job_id, 'dyeing_row_id' => $row->id, 'number' => $roll['number'], 'number_key' => mb_strtolower(trim($roll['number'])), 'weight' => $roll['weight'], 'unit' => $roll['unit'], 'weight_kg' => $roll['weight_kg'], 'is_available' => true, 'created_at' => now(), 'updated_at' => now()]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cutting_job_rolls');
        Schema::dropIfExists('cutting_jobs');
        Schema::dropIfExists('returned_rolls');
    }
};

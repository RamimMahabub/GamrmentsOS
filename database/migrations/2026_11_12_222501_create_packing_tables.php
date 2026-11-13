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
        Schema::create('packing_materials', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('category')->index(); // 'poly_bag', 'size_sticker', 'other_sticker', 'packet_material'
            $table->string('size_code')->nullable()->index(); // e.g. 'S', 'M', 'L', 'XL'
            $table->string('unit')->default('pcs');
            $table->decimal('cost_per_unit', 18, 6)->nullable();
            $table->unsignedInteger('min_stock_alert')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::create('packing_material_stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('packing_material_id')->constrained()->restrictOnDelete();
            $table->string('type')->index(); // 'purchase', 'opening', 'packing_consumption', 'reversal', 'adjustment'
            $table->decimal('quantity', 14, 3); // positive for incoming, negative for consumption
            $table->date('movement_date')->index();
            $table->nullableMorphs('ref');
            $table->decimal('unit_cost', 18, 6)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::create('packing_jobs', function (Blueprint $table): void {
            $table->id();
            $table->date('packing_date')->index();
            $table->foreignId('staff_id')->constrained('staffs')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::create('packing_job_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('packing_job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lot_id')->constrained()->restrictOnDelete();
            $table->string('group_key')->index(); // lot_id:color:dia
            $table->string('color');
            $table->unsignedInteger('dia');
            $table->string('size')->nullable();
            $table->decimal('dozens', 18, 3)->default('0');
            $table->unsignedInteger('packets')->default(0);
            $table->unsignedInteger('pieces')->default(0);
            $table->unsignedInteger('total_pieces')->default(0);
            $table->unsignedInteger('rejected_pieces')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packing_job_rows');
        Schema::dropIfExists('packing_jobs');
        Schema::dropIfExists('packing_material_stock_movements');
        Schema::dropIfExists('packing_materials');
    }
};

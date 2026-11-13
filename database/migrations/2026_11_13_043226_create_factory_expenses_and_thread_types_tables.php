<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function audit(Blueprint $table): void
    {
        $table->timestamps();
        $table->softDeletes();
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('factory_expenses', function (Blueprint $table): void {
            $table->id();
            $table->string('category')->index(); // 'rent', 'electric_bill', 'gas_bill', 'water_bill', 'salary', 'maintenance', 'other'
            $table->string('title');
            $table->decimal('amount', 18, 2);
            $table->date('expense_date')->index();
            $table->string('period_month', 7)->index(); // 'YYYY-MM'
            $table->string('allocation_method', 32)->default('pro_rata_finished_dozens'); // 'pro_rata_finished_dozens', 'lot_specific', 'unallocated'
            $table->foreignId('lot_id')->nullable()->constrained('lots')->nullOnDelete();
            $table->text('notes')->nullable();
            $this->audit($table);
        });

        Schema::create('thread_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('count_code')->nullable(); // e.g. '40/2', '150D'
            $table->decimal('cost_per_cone', 18, 6)->default('0');
            $table->unsignedInteger('meters_per_cone')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $this->audit($table);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thread_types');
        Schema::dropIfExists('factory_expenses');
    }
};

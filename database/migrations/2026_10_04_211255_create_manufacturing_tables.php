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

    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->json('value');
            $this->audit($table);
        });
        Schema::create('companies', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('name_key')->unique();
            $table->text('address');
            $table->boolean('is_knitting')->default(false);
            $table->boolean('is_dyeing')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();
            $this->audit($table);
        });
        Schema::create('company_contacts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('role');
            $table->string('name');
            $table->string('phone', 32);
            $table->unique(['company_id', 'role']);
            $this->audit($table);
        });
        Schema::create('yarns', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->index();
            $table->string('yarn_type');
            $table->string('country_of_origin');
            $table->decimal('price_per_kg', 18, 6);
            $table->string('price_unit', 2);
            $table->decimal('bag_size_kg', 18, 8)->nullable();
            $table->string('bag_size_unit', 2)->nullable();
            $this->audit($table);
        });
        Schema::create('lots', function (Blueprint $table): void {
            $table->id();
            $table->string('lot_number')->unique();
            $table->date('lot_date');
            $table->string('current_stage')->index();
            $this->audit($table);
        });
        Schema::create('lot_sequences', function (Blueprint $table): void {
            $table->id();
            $table->string('base')->unique();
            $table->unsignedInteger('next_suffix')->default(1);
            $this->audit($table);
        });
        Schema::create('knitting_jobs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lot_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->date('entry_date')->index();
            $table->date('delivery_date')->nullable();
            $table->string('input_unit', 2);
            $table->text('notes')->nullable();
            $this->audit($table);
        });
        Schema::create('knitting_job_yarns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('knitting_job_id')->constrained()->restrictOnDelete();
            $table->foreignId('yarn_id')->constrained()->restrictOnDelete();
            $table->unique(['knitting_job_id', 'yarn_id']);
            $this->audit($table);
        });
        Schema::create('knitting_dia_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('knitting_job_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('dia');
            $table->decimal('input_kg', 14, 3);
            $table->decimal('waste_percent', 5, 2)->default(0);
            $table->decimal('output_fabric_kg', 14, 3);
            $table->unsignedInteger('sort_order');
            $table->unique(['knitting_job_id', 'dia']);
            $this->audit($table);
        });
        Schema::create('knitting_dia_row_yarns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('dia_row_id')->constrained('knitting_dia_rows')->restrictOnDelete();
            $table->foreignId('yarn_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity_kg', 14, 3);
            $table->unique(['dia_row_id', 'yarn_id']);
            $this->audit($table);
        });
        Schema::create('yarn_stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('yarn_id')->constrained()->restrictOnDelete();
            $table->string('type');
            $table->decimal('quantity_kg', 14, 3);
            $table->date('movement_date')->index();
            $table->nullableMorphs('ref');
            $table->decimal('price_per_kg', 18, 6)->nullable();
            $table->text('note')->nullable();
            $this->audit($table);
        });
        Schema::create('colors', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('name_key')->unique();
            $table->string('hex', 7)->nullable();
            $this->audit($table);
        });
        Schema::create('dyeing_jobs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lot_id')->constrained()->restrictOnDelete();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->date('entry_date')->index();
            $table->date('return_date')->nullable();
            $table->text('notes')->nullable();
            $this->audit($table);
        });
        Schema::create('dyeing_color_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('dyeing_job_id')->constrained()->restrictOnDelete();
            $table->foreignId('color_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('sort_order');
            $table->unique(['dyeing_job_id', 'color_id']);
            $this->audit($table);
        });
        Schema::create('dyeing_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('color_block_id')->constrained('dyeing_color_blocks')->restrictOnDelete();
            $table->unsignedInteger('dia')->index();
            $table->decimal('given_fabric_kg', 14, 3);
            $table->decimal('returned_fabric_kg', 14, 3)->nullable();
            $table->decimal('waste_percent', 14, 2)->nullable();
            $table->unique(['color_block_id', 'dia']);
            $this->audit($table);
        });
        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('event');
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->index(['subject_type', 'subject_id']);
            $table->json('properties');
            $this->audit($table);
        });
    }

    public function down(): void
    {
        foreach (['activity_logs', 'dyeing_rows', 'dyeing_color_blocks', 'dyeing_jobs', 'colors', 'yarn_stock_movements', 'knitting_dia_row_yarns', 'knitting_dia_rows', 'knitting_job_yarns', 'knitting_jobs', 'lot_sequences', 'lots', 'yarns', 'company_contacts', 'companies', 'settings'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};

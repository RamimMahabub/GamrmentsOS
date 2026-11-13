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
        Schema::table('lots', function (Blueprint $table): void {
            $table->string('product_type', 30)->default('sando')->after('lot_number');
            $table->string('fabric_source', 30)->default('knitted')->after('product_type');
            $table->boolean('has_rib')->default(false)->after('fabric_source');
        });

        Schema::table('returned_rolls', function (Blueprint $table): void {
            $table->foreignId('dyeing_job_id')->nullable()->change();
            $table->foreignId('dyeing_row_id')->nullable()->change();

            $table->foreignId('lot_id')->nullable()->after('dyeing_row_id')->constrained('lots')->nullOnDelete();
            $table->foreignId('color_id')->nullable()->after('lot_id')->constrained('colors')->nullOnDelete();
            $table->unsignedInteger('dia')->nullable()->after('color_id');
            $table->string('supplier_name')->nullable()->after('dia');
            $table->decimal('purchase_price_per_kg', 12, 2)->nullable()->after('supplier_name');
        });

        Schema::table('sewing_job_rows', function (Blueprint $table): void {
            $table->foreignId('source_body_row_id')->nullable()->change();
            $table->json('stage_data')->nullable()->after('group_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sewing_job_rows', function (Blueprint $table): void {
            $table->dropColumn('stage_data');
        });

        Schema::table('returned_rolls', function (Blueprint $table): void {
            $table->dropForeign(['lot_id']);
            $table->dropForeign(['color_id']);
            $table->dropColumn([
                'lot_id',
                'color_id',
                'dia',
                'supplier_name',
                'purchase_price_per_kg',
            ]);
        });

        Schema::table('lots', function (Blueprint $table): void {
            $table->dropColumn([
                'product_type',
                'fabric_source',
                'has_rib',
            ]);
        });
    }
};

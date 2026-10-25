<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dyeing_rows', function (Blueprint $table): void {
            $table->decimal('waste_percent', 20, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('dyeing_rows', function (Blueprint $table): void {
            $table->decimal('waste_percent', 14, 2)->nullable()->change();
        });
    }
};

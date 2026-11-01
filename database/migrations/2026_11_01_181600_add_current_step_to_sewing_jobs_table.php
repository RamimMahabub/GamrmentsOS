<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sewing_jobs', function (Blueprint $table): void {
            $table->string('current_step')->default('fold')->after('staff_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('sewing_jobs', function (Blueprint $table): void {
            $table->dropColumn('current_step');
        });
    }
};

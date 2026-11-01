<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sewing_job_rows', function (Blueprint $table): void {
            foreach (['fold_pieces', 'lock_pieces', 'down_pieces', 'neck_join_pieces', 'neck_finishing_pieces'] as $column) {
                $table->unsignedInteger($column)->default(0)->after(str_replace('_pieces', '_dozens', $column));
            }
        });
    }

    public function down(): void
    {
        Schema::table('sewing_job_rows', function (Blueprint $table): void {
            $table->dropColumn(['fold_pieces', 'lock_pieces', 'down_pieces', 'neck_join_pieces', 'neck_finishing_pieces']);
        });
    }
};

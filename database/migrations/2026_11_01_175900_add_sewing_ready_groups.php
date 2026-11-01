<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sewing_job_rows', function (Blueprint $table): void {
            if (! Schema::hasColumn('sewing_job_rows', 'group_key')) {
                $table->string('group_key')->after('sewing_job_id');
            }
        });
        if (! collect(Schema::getIndexes('sewing_job_rows'))->contains(fn (array $index): bool => $index['name'] === 'sewing_job_rows_sewing_job_id_group_key_unique')) {
            Schema::table('sewing_job_rows', function (Blueprint $table): void {
                $table->unique(['sewing_job_id', 'group_key']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('sewing_job_rows', function (Blueprint $table): void {
            if (collect(Schema::getIndexes('sewing_job_rows'))->contains(fn (array $index): bool => $index['name'] === 'sewing_job_rows_sewing_job_id_group_key_unique')) {
                $table->dropUnique('sewing_job_rows_sewing_job_id_group_key_unique');
            }
            if (Schema::hasColumn('sewing_job_rows', 'group_key')) {
                $table->dropColumn('group_key');
            }
        });
    }
};

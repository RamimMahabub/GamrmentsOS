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
        Schema::table('sewing_job_rows', function (Blueprint $table): void {
            // Quality Check Phase 1
            $table->decimal('qc_phase_1_dozens', 18, 3)->default('0')->after('neck_finishing_pieces');
            $table->unsignedInteger('qc_phase_1_pieces')->default(0)->after('qc_phase_1_dozens');
            $table->unsignedInteger('qc_phase_1_alter_pieces')->default(0)->after('qc_phase_1_pieces');
            $table->unsignedInteger('qc_phase_1_altered_pieces')->default(0)->after('qc_phase_1_alter_pieces');
            $table->unsignedInteger('qc_phase_1_rejected_pieces')->default(0)->after('qc_phase_1_altered_pieces');

            // Iron
            $table->decimal('iron_dozens', 18, 3)->default('0')->after('qc_phase_1_rejected_pieces');
            $table->unsignedInteger('iron_pieces')->default(0)->after('iron_dozens');

            // Quality Check Phase 2
            $table->decimal('qc_phase_2_dozens', 18, 3)->default('0')->after('iron_pieces');
            $table->unsignedInteger('qc_phase_2_pieces')->default(0)->after('qc_phase_2_dozens');
            $table->unsignedInteger('qc_phase_2_alter_pieces')->default(0)->after('qc_phase_2_pieces');
            $table->unsignedInteger('qc_phase_2_altered_pieces')->default(0)->after('qc_phase_2_alter_pieces');
            $table->unsignedInteger('qc_phase_2_rejected_pieces')->default(0)->after('qc_phase_2_altered_pieces');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sewing_job_rows', function (Blueprint $table): void {
            $table->dropColumn([
                'qc_phase_1_dozens',
                'qc_phase_1_pieces',
                'qc_phase_1_alter_pieces',
                'qc_phase_1_altered_pieces',
                'qc_phase_1_rejected_pieces',
                'iron_dozens',
                'iron_pieces',
                'qc_phase_2_dozens',
                'qc_phase_2_pieces',
                'qc_phase_2_alter_pieces',
                'qc_phase_2_altered_pieces',
                'qc_phase_2_rejected_pieces',
            ]);
        });
    }
};

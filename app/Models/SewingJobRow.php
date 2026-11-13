<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SewingJobRow extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'stage_data' => 'array',
        ] + array_fill_keys([
            'fold_dozens', 'lock_dozens', 'down_dozens', 'neck_join_dozens', 'neck_finishing_dozens',
            'qc_phase_1_dozens', 'iron_dozens', 'qc_phase_2_dozens',
        ], 'decimal:3') + array_fill_keys([
            'fold_pieces', 'lock_pieces', 'down_pieces', 'neck_join_pieces', 'neck_finishing_pieces',
            'qc_phase_1_pieces', 'qc_phase_1_alter_pieces', 'qc_phase_1_altered_pieces', 'qc_phase_1_rejected_pieces',
            'iron_pieces',
            'qc_phase_2_pieces', 'qc_phase_2_alter_pieces', 'qc_phase_2_altered_pieces', 'qc_phase_2_rejected_pieces',
            'rejected_pieces',
        ], 'integer');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(SewingJob::class, 'sewing_job_id');
    }

    public function sourceBodyRow(): BelongsTo
    {
        return $this->belongsTo(CuttingJobRoll::class, 'source_body_row_id');
    }

    public function sourceNeckRow(): BelongsTo
    {
        return $this->belongsTo(CuttingJobRoll::class, 'source_neck_row_id');
    }

    public function sourceRibRow(): BelongsTo
    {
        return $this->belongsTo(CuttingJobRoll::class, 'source_rib_row_id');
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }
}

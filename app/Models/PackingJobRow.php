<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackingJobRow extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'dozens' => 'decimal:3',
            'packets' => 'integer',
            'pieces' => 'integer',
            'total_pieces' => 'integer',
            'rejected_pieces' => 'integer',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(PackingJob::class, 'packing_job_id');
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }
}

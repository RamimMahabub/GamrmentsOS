<?php

namespace App\Models;

use App\Support\AuditedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnittingJobYarn extends AuditedModel
{
    public function yarn(): BelongsTo
    {
        return $this->belongsTo(Yarn::class)->withTrashed();
    }

    public function knittingJob(): BelongsTo
    {
        return $this->belongsTo(KnittingJob::class);
    }
}

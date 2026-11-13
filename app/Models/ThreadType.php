<?php

namespace App\Models;

use App\Support\AuditedModel;

class ThreadType extends AuditedModel
{
    protected function casts(): array
    {
        return [
            'cost_per_cone' => 'decimal:6',
            'meters_per_cone' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}

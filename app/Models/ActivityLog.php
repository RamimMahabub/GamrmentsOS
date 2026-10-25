<?php

namespace App\Models;

use App\Support\AuditedModel;

class ActivityLog extends AuditedModel
{
    protected function casts(): array
    {
        return ['properties' => 'array'];
    }
}

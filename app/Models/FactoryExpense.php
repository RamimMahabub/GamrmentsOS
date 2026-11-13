<?php

namespace App\Models;

use App\Support\AuditedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactoryExpense extends AuditedModel
{
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staffs';

    protected $fillable = ['name', 'phone', 'address', 'role', 'is_active'];

    public function cuttingJobs(): HasMany
    {
        return $this->hasMany(CuttingJob::class);
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}

<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

abstract class AuditedModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id', 'created_by', 'updated_by'];

    protected static function booted(): void
    {
        static::creating(function (Model $model): void {
            $model->created_by = Auth::id();
        });
        static::saving(function (Model $model): void {
            $model->updated_by = Auth::id();
        });
        static::deleting(function (Model $model): void {
            if ($model->getTable() !== 'yarn_stock_movements') {
                $model->forceFill(['updated_by' => Auth::id()])->saveQuietly();
            }
        });
        foreach (['created', 'updated', 'deleted'] as $event) {
            static::$event(function (Model $model) use ($event): void {
                if ($event === 'updated' && in_array($model->getTable(), ['knitting_jobs', 'dyeing_jobs'], true)) {
                    return;
                }
                if (in_array($model->getTable(), ['knitting_jobs', 'dyeing_jobs', 'yarn_stock_movements'], true)) {
                    ActivityLog::create(['event' => $event, 'subject_type' => $model->getMorphClass(), 'subject_id' => $model->id, 'properties' => ['before' => $model->getRawOriginal(), 'after' => $model->getAttributes()]]);
                }
            });
        }
    }
}

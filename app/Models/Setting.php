<?php

namespace App\Models;

use App\Support\AuditedModel;

class Setting extends AuditedModel
{
    protected function casts(): array
    {
        return ['value' => 'json'];
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function putValue(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}

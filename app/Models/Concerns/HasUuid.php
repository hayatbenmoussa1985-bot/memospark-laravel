<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Auto-generate a UUID on model creation.
 * Used for iOS sync — the uuid column serves as the external identifier.
 */
trait HasUuid
{
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the route key name for route model binding via UUID.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Find a model by its UUID.
     */
    public static function findByUuid(string $uuid): ?static
    {
        return static::where('uuid', $uuid)->first();
    }

    /**
     * Find a model by its UUID or fail.
     */
    public static function findByUuidOrFail(string $uuid): static
    {
        return static::where('uuid', $uuid)->firstOrFail();
    }
}

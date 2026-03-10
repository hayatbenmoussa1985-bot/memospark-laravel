<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
        'description',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'updated_at' => 'datetime',
        ];
    }

    // ──────────────────────────────────────────────
    // Static helpers
    // ──────────────────────────────────────────────

    /**
     * Get a setting value by key.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        return Cache::remember("app_setting:{$key}", 3600, function () use ($key, $default) {
            $setting = static::find($key);
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value.
     */
    public static function setValue(string $key, mixed $value, ?string $description = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            array_filter([
                'value' => $value,
                'description' => $description,
                'updated_at' => now(),
            ])
        );

        Cache::forget("app_setting:{$key}");
    }

    /**
     * Get multiple settings at once.
     */
    public static function getValues(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = static::getValue($key);
        }
        return $result;
    }
}

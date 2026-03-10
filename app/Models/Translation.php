<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'translatable_type',
        'translatable_id',
        'locale',
        'field',
        'value',
    ];

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeForModel($query, string $type, int $id)
    {
        return $query->where('translatable_type', $type)
            ->where('translatable_id', $id);
    }

    public function scopeLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    public function scopeField($query, string $field)
    {
        return $query->where('field', $field);
    }

    // ──────────────────────────────────────────────
    // Static helpers
    // ──────────────────────────────────────────────

    /**
     * Get a translated value for a model.
     */
    public static function getTranslation(string $type, int $id, string $field, ?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();

        $translation = static::forModel($type, $id)
            ->locale($locale)
            ->field($field)
            ->first();

        // Fallback to English if translation not found
        if (!$translation && $locale !== 'en') {
            $translation = static::forModel($type, $id)
                ->locale('en')
                ->field($field)
                ->first();
        }

        return $translation?->value;
    }

    /**
     * Set a translation for a model.
     */
    public static function setTranslation(string $type, int $id, string $field, string $locale, string $value): static
    {
        return static::updateOrCreate(
            [
                'translatable_type' => $type,
                'translatable_id' => $id,
                'locale' => $locale,
                'field' => $field,
            ],
            ['value' => $value]
        );
    }
}

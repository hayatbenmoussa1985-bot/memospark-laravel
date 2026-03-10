<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'icon',
        'color',
        'criteria',
    ];

    protected function casts(): array
    {
        return [
            'criteria' => 'array',
        ];
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    /**
     * Users who earned this badge.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('awarded_by', 'awarded_at');
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * Get the translated name for the current locale.
     */
    public function translatedName(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        $translation = Translation::where('translatable_type', 'badge')
            ->where('translatable_id', $this->id)
            ->where('locale', $locale)
            ->where('field', 'name')
            ->first();

        return $translation?->value ?? $this->name;
    }
}

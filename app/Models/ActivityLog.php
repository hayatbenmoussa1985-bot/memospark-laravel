<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'activity_type',
        'deck_id',
        'metadata',
        'duration_minutes',
        'cards_reviewed',
        'success_rate',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'duration_minutes' => 'integer',
            'cards_reviewed' => 'integer',
            'success_rate' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeOfType($query, string $type)
    {
        return $query->where('activity_type', $type);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    // ──────────────────────────────────────────────
    // Static helpers
    // ──────────────────────────────────────────────

    /**
     * Log a study activity.
     */
    public static function logStudy(int $userId, int $deckId, int $cardsReviewed, float $successRate, int $durationMinutes): static
    {
        return static::create([
            'user_id' => $userId,
            'activity_type' => 'study',
            'deck_id' => $deckId,
            'cards_reviewed' => $cardsReviewed,
            'success_rate' => $successRate,
            'duration_minutes' => $durationMinutes,
            'created_at' => now(),
        ]);
    }

    /**
     * Log a generic activity.
     */
    public static function log(int $userId, string $type, ?int $deckId = null, ?array $metadata = null): static
    {
        return static::create([
            'user_id' => $userId,
            'activity_type' => $type,
            'deck_id' => $deckId,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}

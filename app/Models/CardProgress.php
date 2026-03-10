<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardProgress extends Model
{
    protected $table = 'card_progress';

    protected $fillable = [
        'user_id',
        'card_id',
        'easiness_factor',
        'interval_days',
        'repetitions',
        'next_review_at',
        'last_reviewed_at',
        'total_reviews',
        'correct_reviews',
    ];

    protected function casts(): array
    {
        return [
            'easiness_factor' => 'decimal:2',
            'interval_days' => 'integer',
            'repetitions' => 'integer',
            'next_review_at' => 'datetime',
            'last_reviewed_at' => 'datetime',
            'total_reviews' => 'integer',
            'correct_reviews' => 'integer',
        ];
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * Check if this card is due for review.
     */
    public function isDue(): bool
    {
        return $this->next_review_at <= now();
    }

    /**
     * Get the accuracy rate as a percentage.
     */
    public function accuracyRate(): float
    {
        if ($this->total_reviews === 0) {
            return 0;
        }

        return round(($this->correct_reviews / $this->total_reviews) * 100, 1);
    }
}

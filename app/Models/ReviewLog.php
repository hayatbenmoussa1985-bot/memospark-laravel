<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'card_id',
        'session_id',
        'quality',
        'easiness_factor_before',
        'easiness_factor_after',
        'interval_before',
        'interval_after',
        'time_spent_ms',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'quality' => 'integer',
            'easiness_factor_before' => 'decimal:2',
            'easiness_factor_after' => 'decimal:2',
            'interval_before' => 'integer',
            'interval_after' => 'integer',
            'time_spent_ms' => 'integer',
            'reviewed_at' => 'datetime',
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

    public function session(): BelongsTo
    {
        return $this->belongsTo(StudySession::class, 'session_id');
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * Check if this review was correct (quality >= 3 in SM-2).
     */
    public function isCorrect(): bool
    {
        return $this->quality >= 3;
    }
}

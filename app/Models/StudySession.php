<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudySession extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'deck_id',
        'cards_reviewed',
        'correct_count',
        'duration_seconds',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'cards_reviewed' => 'integer',
            'correct_count' => 'integer',
            'duration_seconds' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
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

    public function reviewLogs(): HasMany
    {
        return $this->hasMany(ReviewLog::class, 'session_id');
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * Get the accuracy rate for this session.
     */
    public function accuracyRate(): float
    {
        if ($this->cards_reviewed === 0) {
            return 0;
        }

        return round(($this->correct_count / $this->cards_reviewed) * 100, 1);
    }

    /**
     * Check if the session is still in progress.
     */
    public function isInProgress(): bool
    {
        return is_null($this->completed_at);
    }

    /**
     * Get duration in a human-readable format.
     */
    public function formattedDuration(): string
    {
        $minutes = intdiv($this->duration_seconds, 60);
        $seconds = $this->duration_seconds % 60;

        if ($minutes > 0) {
            return sprintf('%dm %ds', $minutes, $seconds);
        }

        return sprintf('%ds', $seconds);
    }
}

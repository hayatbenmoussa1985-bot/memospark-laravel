<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Card extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid',
        'deck_id',
        'front_text',
        'back_text',
        'front_image_url',
        'back_image_url',
        'front_audio_url',
        'back_audio_url',
        'hint',
        'explanation',
        'position',
        'is_mcq',
        'mcq_question',
    ];

    protected function casts(): array
    {
        return [
            'is_mcq' => 'boolean',
            'position' => 'integer',
        ];
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    /**
     * Deck this card belongs to.
     */
    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    /**
     * MCQ options for this card (if is_mcq = true).
     */
    public function mcqOptions(): HasMany
    {
        return $this->hasMany(McqOption::class)->orderBy('position');
    }

    /**
     * Progress records for this card (across all users).
     */
    public function progress(): HasMany
    {
        return $this->hasMany(CardProgress::class);
    }

    /**
     * Get progress for a specific user.
     */
    public function progressForUser(int $userId): ?CardProgress
    {
        return $this->progress()->where('user_id', $userId)->first();
    }

    /**
     * Review logs for this card.
     */
    public function reviewLogs(): HasMany
    {
        return $this->hasMany(ReviewLog::class);
    }

    /**
     * AI generated content for this card.
     */
    public function aiGeneratedContent(): HasMany
    {
        return $this->hasMany(AiGeneratedContent::class);
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * Check if this card is due for review by a specific user.
     */
    public function isDueForUser(int $userId): bool
    {
        $progress = $this->progressForUser($userId);

        if (!$progress) {
            return true; // Never studied = due
        }

        return $progress->next_review_at <= now();
    }
}

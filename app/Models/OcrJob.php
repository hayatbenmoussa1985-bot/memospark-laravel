<?php

namespace App\Models;

use App\Enums\OcrJobStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OcrJob extends Model
{
    protected $fillable = [
        'user_id',
        'image_path',
        'status',
        'job_type',
        'result_deck_id',
        'error_message',
        'n8n_webhook_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => OcrJobStatus::class,
        ];
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The deck that was generated from this OCR job.
     */
    public function resultDeck(): BelongsTo
    {
        return $this->belongsTo(Deck::class, 'result_deck_id');
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * Mark the job as processing.
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => OcrJobStatus::Processing]);
    }

    /**
     * Mark the job as completed with a result deck.
     */
    public function markAsCompleted(int $deckId): void
    {
        $this->update([
            'status' => OcrJobStatus::Completed,
            'result_deck_id' => $deckId,
        ]);
    }

    /**
     * Mark the job as failed with an error message.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => OcrJobStatus::Failed,
            'error_message' => $errorMessage,
        ]);
    }
}

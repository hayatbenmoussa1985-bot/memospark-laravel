<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = [
        'reporter_id',
        'reportable_type',
        'reportable_id',
        'reason',
        'description',
        'status',
        'reviewed_by',
        'resolution_note',
    ];

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeResolved($query)
    {
        return $query->whereIn('status', ['resolved', 'dismissed']);
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ──────────────────────────────────────────────
    // Polymorphic target resolution
    // ──────────────────────────────────────────────

    /**
     * Get the reported model (deck, card, or user).
     */
    public function reportable(): ?Model
    {
        $modelClass = match ($this->reportable_type) {
            'deck' => Deck::class,
            'card' => Card::class,
            'user' => User::class,
            default => null,
        };

        if (!$modelClass) {
            return null;
        }

        return $modelClass::find($this->reportable_id);
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * Resolve the report.
     */
    public function resolve(int $reviewerId, string $note = ''): void
    {
        $this->update([
            'status' => 'resolved',
            'reviewed_by' => $reviewerId,
            'resolution_note' => $note,
        ]);
    }

    /**
     * Dismiss the report.
     */
    public function dismiss(int $reviewerId, string $note = ''): void
    {
        $this->update([
            'status' => 'dismissed',
            'reviewed_by' => $reviewerId,
            'resolution_note' => $note,
        ]);
    }
}

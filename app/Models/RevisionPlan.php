<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RevisionPlan extends Model
{
    protected $fillable = [
        'parent_id',
        'child_id',
        'title',
        'description',
        'daily_goal_cards',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'daily_goal_cards' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    /**
     * The parent who created this plan.
     */
    public function parentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * The child this plan is for.
     */
    public function childUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'child_id');
    }

    /**
     * Decks included in this revision plan.
     */
    public function decks(): BelongsToMany
    {
        return $this->belongsToMany(Deck::class, 'revision_plan_decks', 'plan_id', 'deck_id');
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * Check if the plan is currently active (within date range).
     */
    public function isCurrentlyActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $today = now()->toDateString();

        if ($this->start_date > $today) {
            return false;
        }

        if ($this->end_date && $this->end_date < $today) {
            return false;
        }

        return true;
    }
}

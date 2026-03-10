<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'apple_transaction_id',
        'apple_original_transaction_id',
        'current_period_start',
        'current_period_end',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'trial'])
            ->where('current_period_end', '>', now());
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * Check if the subscription is currently active.
     */
    public function isActive(): bool
    {
        return $this->status->grantsAccess() && $this->current_period_end > now();
    }

    /**
     * Check if the subscription has expired.
     */
    public function isExpired(): bool
    {
        return $this->current_period_end <= now();
    }

    /**
     * Get the number of days remaining.
     */
    public function daysRemaining(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return (int) now()->diffInDays($this->current_period_end);
    }
}

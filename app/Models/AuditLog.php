<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'target_type',
        'target_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeForTarget($query, string $type, int $id)
    {
        return $query->where('target_type', $type)
            ->where('target_id', $id);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    // ──────────────────────────────────────────────
    // Static helpers
    // ──────────────────────────────────────────────

    /**
     * Record an audit log entry.
     */
    public static function record(
        int $userId,
        string $action,
        string $targetType,
        int $targetId,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): static {
        return static::create([
            'user_id' => $userId,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}

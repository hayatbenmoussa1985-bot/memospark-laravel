<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentChild extends Model
{
    protected $table = 'parent_child';

    public $timestamps = false;

    protected $fillable = [
        'parent_id',
        'child_id',
        'relationship',
        'permissions',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'created_at' => 'datetime',
        ];
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function parentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function childUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'child_id');
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * Check if the parent has a specific permission on this child.
     */
    public function hasPermission(string $key): bool
    {
        $perms = $this->permissions ?? [];
        return $perms[$key] ?? false;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPost extends Model
{
    protected $fillable = [
        'slug',
        'author_id',
        'title',
        'excerpt',
        'content',
        'cover_image_path',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderByDesc('published_at');
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * Check if the post is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published'
            && $this->published_at
            && $this->published_at <= now();
    }

    /**
     * Get the route key for URL generation.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

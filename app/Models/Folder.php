<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'color',
        'icon',
        'parent_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parentFolder(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function childFolders(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function decks(): BelongsToMany
    {
        return $this->belongsToMany(Deck::class, 'deck_folder')
            ->withPivot('user_id', 'sort_order');
    }
}

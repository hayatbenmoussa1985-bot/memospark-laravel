<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGeneratedContent extends Model
{
    public $timestamps = false;

    protected $table = 'ai_generated_content';

    protected $fillable = [
        'deck_id',
        'card_id',
        'prompt_used',
        'image_url',
        'generation_source',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}

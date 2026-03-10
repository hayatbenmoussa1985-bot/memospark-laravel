<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class McqOption extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'card_id',
        'option_text',
        'option_image_url',
        'is_correct',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'position' => 'integer',
        ];
    }

    /**
     * Card this option belongs to.
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}

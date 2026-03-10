<?php

namespace App\Models;

use App\Enums\DeckDifficulty;
use App\Enums\DeckVisibility;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deck extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'title',
        'description',
        'category_id',
        'language',
        'difficulty',
        'visibility',
        'cover_image_path',
        'is_featured',
        'cards_count',
        'average_rating',
        'ratings_count',
        'is_ai_generated',
    ];

    protected function casts(): array
    {
        return [
            'difficulty' => DeckDifficulty::class,
            'visibility' => DeckVisibility::class,
            'is_featured' => 'boolean',
            'is_ai_generated' => 'boolean',
            'cards_count' => 'integer',
            'average_rating' => 'decimal:2',
            'ratings_count' => 'integer',
        ];
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopePublic($query)
    {
        return $query->whereIn('visibility', ['public', 'library']);
    }

    public function scopeLibrary($query)
    {
        return $query->where('visibility', 'library');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    /**
     * Author of this deck.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Alias for user — more semantic.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Category this deck belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Cards in this deck.
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class)->orderBy('position');
    }

    /**
     * Study sessions for this deck.
     */
    public function studySessions(): HasMany
    {
        return $this->hasMany(StudySession::class);
    }

    /**
     * Users who favorited this deck.
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'deck_favorites')
            ->withTimestamps();
    }

    /**
     * Ratings for this deck.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(DeckRating::class);
    }

    /**
     * Folders this deck is organized into.
     */
    public function folders(): BelongsToMany
    {
        return $this->belongsToMany(Folder::class, 'deck_folder')
            ->withPivot('user_id', 'sort_order');
    }

    /**
     * Revision plans that include this deck.
     */
    public function revisionPlans(): BelongsToMany
    {
        return $this->belongsToMany(RevisionPlan::class, 'revision_plan_decks', 'deck_id', 'plan_id');
    }

    /**
     * Activity logs related to this deck.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * AI generated content for this deck.
     */
    public function aiGeneratedContent(): HasMany
    {
        return $this->hasMany(AiGeneratedContent::class);
    }

    /**
     * OCR job that created this deck (if any).
     */
    public function ocrJob(): HasMany
    {
        return $this->hasMany(OcrJob::class, 'result_deck_id');
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * Recalculate the cached cards_count.
     */
    public function updateCardsCount(): void
    {
        $this->update(['cards_count' => $this->cards()->count()]);
    }

    /**
     * Recalculate average rating from deck_ratings.
     */
    public function recalculateRating(): void
    {
        $stats = $this->ratings()->selectRaw('AVG(rating) as avg, COUNT(*) as cnt')->first();
        $this->update([
            'average_rating' => round($stats->avg ?? 0, 2),
            'ratings_count' => $stats->cnt ?? 0,
        ]);
    }
}

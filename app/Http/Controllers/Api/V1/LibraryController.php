<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Card;
use App\Models\CardProgress;
use App\Models\Category;
use App\Models\Deck;
use App\Models\DeckRating;
use App\Models\Report;
use App\Services\SM2Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LibraryController extends Controller
{
    public function __construct(
        private SM2Service $sm2Service,
    ) {}

    // ══════════════════════════════════════════════
    // Categories
    // ══════════════════════════════════════════════

    /**
     * GET /library/categories
     */
    public function categories(): JsonResponse
    {
        $categories = Category::active()
            ->roots()
            ->ordered()
            ->with('childCategories')
            ->withCount(['decks' => fn ($q) => $q->where('visibility', 'library')])
            ->get();

        return response()->json([
            'data' => $categories->map(fn ($c) => [
                'id' => $c->id,
                'slug' => $c->slug,
                'name' => $c->translatedName(),
                'icon' => $c->icon,
                'decks_count' => $c->decks_count ?? 0,
                'children' => $c->childCategories->map(fn ($child) => [
                    'id' => $child->id,
                    'slug' => $child->slug,
                    'name' => $child->translatedName(),
                    'icon' => $child->icon,
                ]),
            ]),
        ]);
    }

    /**
     * GET /library/categories/{slug}
     */
    public function showCategory(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)
            ->with('childCategories')
            ->firstOrFail();

        $decks = Deck::library()
            ->where('category_id', $category->id)
            ->withCount('cards')
            ->orderByDesc('average_rating')
            ->get();

        return response()->json([
            'category' => [
                'id' => $category->id,
                'slug' => $category->slug,
                'name' => $category->translatedName(),
                'icon' => $category->icon,
            ],
            'decks' => $decks->map(fn ($d) => $this->formatLibraryDeck($d)),
        ]);
    }

    // ══════════════════════════════════════════════
    // Decks
    // ══════════════════════════════════════════════

    /**
     * GET /library/decks
     */
    public function decks(Request $request): JsonResponse
    {
        $query = Deck::library()->withCount('cards')->with('user:id,name');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->has('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }
        if ($request->has('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }
        if ($request->has('language')) {
            $query->where('language', $request->language);
        }
        if ($request->has('search') || $request->has('q')) {
            $search = $request->search ?? $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $decks = $query->orderByDesc('average_rating')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => $decks->getCollection()->map(fn ($d) => $this->formatLibraryDeck($d)),
            'meta' => [
                'current_page' => $decks->currentPage(),
                'per_page' => $decks->perPage(),
                'last_page' => $decks->lastPage(),
                'total' => $decks->total(),
            ],
        ]);
    }

    /**
     * GET /library/decks/featured
     */
    public function featured(): JsonResponse
    {
        $decks = Deck::library()
            ->featured()
            ->withCount('cards')
            ->with('user:id,name')
            ->orderByDesc('average_rating')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => $decks->map(fn ($d) => $this->formatLibraryDeck($d)),
        ]);
    }

    /**
     * GET /library/decks/{identifier}
     * Accepts UUID or slug.
     */
    public function showDeck(string $identifier): JsonResponse
    {
        $deck = Deck::where('visibility', 'library')
            ->where(function ($q) use ($identifier) {
                $q->where('uuid', $identifier)
                  ->orWhere('id', is_numeric($identifier) ? $identifier : 0);
            })
            ->withCount('cards')
            ->with('user:id,name')
            ->firstOrFail();

        return response()->json($this->formatLibraryDeck($deck));
    }

    /**
     * GET /library/decks/{identifier}/cards
     */
    public function deckCards(Request $request, string $identifier): JsonResponse
    {
        $deck = Deck::where('visibility', 'library')
            ->where(function ($q) use ($identifier) {
                $q->where('uuid', $identifier)
                  ->orWhere('id', is_numeric($identifier) ? $identifier : 0);
            })
            ->firstOrFail();

        $query = $deck->cards()->with('mcqOptions')->orderBy('position');

        if ($request->has('per_page')) {
            $cards = $query->paginate($request->get('per_page', 20));
            return response()->json([
                'data' => $cards->getCollection()->map(fn ($c) => $this->formatCard($c)),
                'meta' => [
                    'current_page' => $cards->currentPage(),
                    'per_page' => $cards->perPage(),
                    'total' => $cards->total(),
                ],
            ]);
        }

        $cards = $query->get();
        return response()->json([
            'data' => $cards->map(fn ($c) => $this->formatCard($c)),
        ]);
    }

    /**
     * GET /library/cards/{id}
     */
    public function showCard(int $id): JsonResponse
    {
        $card = Card::with('mcqOptions')->findOrFail($id);

        return response()->json($this->formatCard($card));
    }

    // ══════════════════════════════════════════════
    // Study Progress
    // ══════════════════════════════════════════════

    /**
     * POST /library/decks/{identifier}/start
     * POST /library/progress/{identifier}/start
     */
    public function startDeck(Request $request, string $identifier): JsonResponse
    {
        $deck = $this->resolveLibraryDeck($identifier);

        return response()->json([
            'message' => 'Deck started.',
            'deck_uuid' => $deck->uuid,
            'deck_id' => $deck->id,
            'cards_count' => $deck->cards()->count(),
        ]);
    }

    /**
     * GET /library/progress
     * Get user's global library progress.
     */
    public function globalProgress(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Get all library deck IDs
        $libraryDeckIds = Deck::library()->pluck('id');

        $progress = CardProgress::where('user_id', $userId)
            ->whereIn('card_id', function ($q) use ($libraryDeckIds) {
                $q->select('id')->from('cards')->whereIn('deck_id', $libraryDeckIds);
            })
            ->selectRaw('COUNT(*) as cards_studied, SUM(total_reviews) as total_reviews, SUM(correct_reviews) as correct_reviews')
            ->first();

        return response()->json([
            'total_cards_studied' => (int) ($progress->cards_studied ?? 0),
            'total_reviews' => (int) ($progress->total_reviews ?? 0),
            'correct_reviews' => (int) ($progress->correct_reviews ?? 0),
            'accuracy_rate' => ($progress->total_reviews ?? 0) > 0
                ? round(($progress->correct_reviews / $progress->total_reviews) * 100, 1)
                : 0,
        ]);
    }

    /**
     * GET /library/progress/{identifier}
     * Get user's progress for a specific library deck.
     */
    public function deckProgress(Request $request, string $identifier): JsonResponse
    {
        $deck = $this->resolveLibraryDeck($identifier);
        $userId = $request->user()->id;

        $cardIds = $deck->cards()->pluck('id');
        $totalCards = $cardIds->count();

        $progress = CardProgress::where('user_id', $userId)
            ->whereIn('card_id', $cardIds)
            ->get();

        $cardsStudied = $progress->count();
        $totalReviews = $progress->sum('total_reviews');
        $correctReviews = $progress->sum('correct_reviews');

        $dueCards = $progress->where('next_review_at', '<=', now())->count();
        $newCards = $totalCards - $cardsStudied;

        return response()->json([
            'deck_id' => $deck->id,
            'deck_uuid' => $deck->uuid,
            'total_cards' => $totalCards,
            'cards_studied' => $cardsStudied,
            'new_cards' => $newCards,
            'due_cards' => $dueCards,
            'total_reviews' => (int) $totalReviews,
            'correct_reviews' => (int) $correctReviews,
            'accuracy_rate' => $totalReviews > 0
                ? round(($correctReviews / $totalReviews) * 100, 1)
                : 0,
            'completion_rate' => $totalCards > 0
                ? round(($cardsStudied / $totalCards) * 100, 1)
                : 0,
        ]);
    }

    /**
     * GET /library/progress/{identifier}/study
     * Get cards to study for a deck (due + new cards).
     */
    public function studyCards(Request $request, string $identifier): JsonResponse
    {
        $deck = $this->resolveLibraryDeck($identifier);
        $userId = $request->user()->id;
        $limit = $request->get('limit', 20);

        // Get due cards (already studied, need review)
        $dueCardIds = CardProgress::where('user_id', $userId)
            ->whereIn('card_id', $deck->cards()->pluck('id'))
            ->where('next_review_at', '<=', now())
            ->orderBy('next_review_at')
            ->pluck('card_id');

        // Get new cards (not yet studied)
        $newCardIds = $deck->cards()
            ->whereNotIn('id', CardProgress::where('user_id', $userId)->pluck('card_id'))
            ->orderBy('position')
            ->pluck('id');

        // Mix: due first, then new
        $cardIds = $dueCardIds->merge($newCardIds)->take($limit);

        $cards = Card::with('mcqOptions')
            ->whereIn('id', $cardIds)
            ->get()
            ->sortBy(fn ($c) => $cardIds->search($c->id));

        return response()->json([
            'data' => $cards->values()->map(fn ($c) => $this->formatCard($c)),
            'meta' => [
                'total_due' => $dueCardIds->count(),
                'total_new' => $newCardIds->count(),
            ],
        ]);
    }

    /**
     * POST /library/progress/{identifier}/complete
     */
    public function completeDeckStudy(Request $request, string $identifier): JsonResponse
    {
        $deck = $this->resolveLibraryDeck($identifier);

        return response()->json([
            'message' => 'Study session completed.',
            'deck_uuid' => $deck->uuid,
        ]);
    }

    /**
     * POST /library/progress/{identifier}/review
     * POST /library/cards/{id}/review
     */
    public function reviewCard(Request $request, $identifier): JsonResponse
    {
        $request->validate([
            'quality' => 'required|integer|min:0|max:5',
            'time_spent_ms' => 'nullable|integer|min:0',
            'card_id' => 'nullable|integer',
        ]);

        // If identifier is numeric, it's a card ID directly
        // If it's a deck identifier, use card_id from request
        if (is_numeric($identifier) && !$request->has('card_id')) {
            $cardId = (int) $identifier;
        } else {
            $cardId = $request->card_id ?? (int) $identifier;
        }

        $card = Card::findOrFail($cardId);

        $progress = $this->sm2Service->review(
            userId: $request->user()->id,
            cardId: $card->id,
            quality: $request->quality,
            timeSpentMs: $request->time_spent_ms,
        );

        return response()->json([
            'progress' => [
                'easiness_factor' => $progress->easiness_factor,
                'interval_days' => $progress->interval_days,
                'next_review_at' => $progress->next_review_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * POST /library/cards/{cardId}/answer-qcm
     */
    public function answerQcm(Request $request, int $cardId): JsonResponse
    {
        $request->validate([
            'option_id' => 'required|integer',
        ]);

        $card = Card::with('mcqOptions')->findOrFail($cardId);
        $selectedOption = $card->mcqOptions->firstWhere('id', $request->option_id);

        if (!$selectedOption) {
            return response()->json(['message' => 'Option not found.'], 404);
        }

        $isCorrect = $selectedOption->is_correct;

        // Record as SM-2 review: quality 5 for correct, 1 for wrong
        $quality = $isCorrect ? 5 : 1;
        $progress = $this->sm2Service->review(
            userId: $request->user()->id,
            cardId: $card->id,
            quality: $quality,
        );

        return response()->json([
            'is_correct' => $isCorrect,
            'correct_option_id' => $card->mcqOptions->firstWhere('is_correct', true)?->id,
            'progress' => [
                'easiness_factor' => $progress->easiness_factor,
                'interval_days' => $progress->interval_days,
                'next_review_at' => $progress->next_review_at->toIso8601String(),
            ],
        ]);
    }

    // ══════════════════════════════════════════════
    // Favorites
    // ══════════════════════════════════════════════

    /**
     * GET /library/favorites
     */
    public function favorites(Request $request): JsonResponse
    {
        $decks = $request->user()
            ->favoriteDecks()
            ->where('visibility', 'library')
            ->withCount('cards')
            ->get();

        return response()->json([
            'data' => $decks->map(fn ($d) => $this->formatLibraryDeck($d)),
        ]);
    }

    /**
     * POST /library/favorites/{deckId}
     */
    public function addFavorite(Request $request, $deckId): JsonResponse
    {
        $deck = Deck::where('visibility', 'library')
            ->where(function ($q) use ($deckId) {
                $q->where('id', is_numeric($deckId) ? $deckId : 0)
                  ->orWhere('uuid', $deckId);
            })
            ->firstOrFail();

        $exists = DB::table('deck_favorites')
            ->where('user_id', $request->user()->id)
            ->where('deck_id', $deck->id)
            ->exists();

        if (!$exists) {
            DB::table('deck_favorites')->insert([
                'user_id' => $request->user()->id,
                'deck_id' => $deck->id,
                'created_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Added to favorites.']);
    }

    /**
     * DELETE /library/favorites/{deckId}
     */
    public function removeFavorite(Request $request, $deckId): JsonResponse
    {
        $deck = Deck::where(function ($q) use ($deckId) {
                $q->where('id', is_numeric($deckId) ? $deckId : 0)
                  ->orWhere('uuid', $deckId);
            })
            ->firstOrFail();

        DB::table('deck_favorites')
            ->where('user_id', $request->user()->id)
            ->where('deck_id', $deck->id)
            ->delete();

        return response()->json(['message' => 'Removed from favorites.']);
    }

    // ══════════════════════════════════════════════
    // Badges (library context)
    // ══════════════════════════════════════════════

    /**
     * GET /library/badges
     */
    public function badges(): JsonResponse
    {
        $badges = Badge::all();

        return response()->json([
            'data' => $badges->map(fn ($b) => [
                'id' => $b->id,
                'slug' => $b->slug,
                'name' => $b->translatedName(),
                'description' => $b->description,
                'icon' => $b->icon,
                'color' => $b->color,
            ]),
        ]);
    }

    /**
     * GET /library/badges/earned
     */
    public function earnedBadges(Request $request): JsonResponse
    {
        $badges = $request->user()->badges()->get();

        return response()->json([
            'data' => $badges->map(fn ($b) => [
                'id' => $b->id,
                'slug' => $b->slug,
                'name' => $b->translatedName(),
                'icon' => $b->icon,
                'color' => $b->color,
                'awarded_at' => $b->pivot->awarded_at,
            ]),
        ]);
    }

    /**
     * GET /library/badges/{slug}
     */
    public function showBadge(string $slug): JsonResponse
    {
        $badge = Badge::where('slug', $slug)->firstOrFail();

        return response()->json([
            'id' => $badge->id,
            'slug' => $badge->slug,
            'name' => $badge->translatedName(),
            'description' => $badge->description,
            'icon' => $badge->icon,
            'color' => $badge->color,
            'criteria' => $badge->criteria,
        ]);
    }

    // ══════════════════════════════════════════════
    // Ratings
    // ══════════════════════════════════════════════

    /**
     * POST /library/decks/{deckId}/rate
     */
    public function rateDeck(Request $request, $deckId): JsonResponse
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $deck = Deck::where(function ($q) use ($deckId) {
                $q->where('id', is_numeric($deckId) ? $deckId : 0)
                  ->orWhere('uuid', $deckId);
            })
            ->where('visibility', 'library')
            ->firstOrFail();

        DeckRating::updateOrCreate(
            ['user_id' => $request->user()->id, 'deck_id' => $deck->id],
            ['rating' => $request->rating],
        );

        // Update cached average
        $stats = DeckRating::where('deck_id', $deck->id)
            ->selectRaw('AVG(rating) as avg, COUNT(*) as cnt')
            ->first();

        $deck->update([
            'average_rating' => round($stats->avg, 2),
            'ratings_count' => $stats->cnt,
        ]);

        return response()->json([
            'message' => 'Rating saved.',
            'average_rating' => round($stats->avg, 2),
            'ratings_count' => (int) $stats->cnt,
        ]);
    }

    /**
     * GET /library/decks/{deckId}/my-rating
     */
    public function myRating(Request $request, $deckId): JsonResponse
    {
        $deck = Deck::where(function ($q) use ($deckId) {
                $q->where('id', is_numeric($deckId) ? $deckId : 0)
                  ->orWhere('uuid', $deckId);
            })
            ->firstOrFail();

        $rating = DeckRating::where('user_id', $request->user()->id)
            ->where('deck_id', $deck->id)
            ->first();

        return response()->json([
            'rating' => $rating?->rating,
            'has_rated' => !is_null($rating),
        ]);
    }

    // ══════════════════════════════════════════════
    // Reports
    // ══════════════════════════════════════════════

    /**
     * POST /library/reports
     */
    public function submitReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reportable_type' => 'required|in:deck,card,user',
            'reportable_id' => 'required|integer',
            'reason' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        Report::create([
            'reporter_id' => $request->user()->id,
            'reportable_type' => $validated['reportable_type'],
            'reportable_id' => $validated['reportable_id'],
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json(['message' => 'Report submitted.'], 201);
    }

    // ══════════════════════════════════════════════
    // Private helpers
    // ══════════════════════════════════════════════

    private function resolveLibraryDeck(string $identifier): Deck
    {
        return Deck::where('visibility', 'library')
            ->where(function ($q) use ($identifier) {
                $q->where('uuid', $identifier)
                  ->orWhere('id', is_numeric($identifier) ? $identifier : 0);
            })
            ->firstOrFail();
    }

    private function formatLibraryDeck(Deck $deck): array
    {
        return [
            'id' => $deck->id,
            'uuid' => $deck->uuid,
            'slug' => $deck->uuid, // Alias for mobile compatibility
            'title' => $deck->title,
            'description' => $deck->description,
            'language' => $deck->language,
            'difficulty' => $deck->difficulty?->value,
            'category_id' => $deck->category_id,
            'cover_image_path' => $deck->cover_image_path,
            'is_featured' => $deck->is_featured,
            'cards_count' => $deck->cards_count ?? 0,
            'average_rating' => $deck->average_rating,
            'ratings_count' => $deck->ratings_count,
            'author' => $deck->user ? ['name' => $deck->user->name] : null,
        ];
    }

    private function formatCard(Card $card): array
    {
        return [
            'id' => $card->id,
            'uuid' => $card->uuid,
            'front_text' => $card->front_text,
            'back_text' => $card->back_text,
            'front_image_url' => $card->front_image_url,
            'back_image_url' => $card->back_image_url,
            'front_audio_url' => $card->front_audio_url,
            'back_audio_url' => $card->back_audio_url,
            'hint' => $card->hint,
            'explanation' => $card->explanation,
            'position' => $card->position,
            'is_mcq' => $card->is_mcq,
            'mcq_question' => $card->mcq_question,
            'mcq_options' => $card->mcqOptions?->map(fn ($o) => [
                'id' => $o->id,
                'option_text' => $o->option_text,
                'option_image_url' => $o->option_image_url,
                'is_correct' => $o->is_correct,
                'position' => $o->position,
            ]),
        ];
    }
}

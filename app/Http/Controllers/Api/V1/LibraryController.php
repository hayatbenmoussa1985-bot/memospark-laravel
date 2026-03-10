<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Category;
use App\Models\Deck;
use App\Services\SM2Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function __construct(
        private SM2Service $sm2Service,
    ) {}

    /**
     * GET /api/v1/library/categories
     */
    public function categories(): JsonResponse
    {
        $categories = Category::active()
            ->roots()
            ->ordered()
            ->with('childCategories')
            ->get();

        return response()->json([
            'categories' => $categories->map(fn ($c) => [
                'id' => $c->id,
                'slug' => $c->slug,
                'name' => $c->translatedName(),
                'icon' => $c->icon,
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
     * GET /api/v1/library/decks
     */
    public function decks(Request $request): JsonResponse
    {
        $query = Deck::library()->withCount('cards');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        if ($request->has('language')) {
            $query->where('language', $request->language);
        }

        $decks = $query->orderByDesc('average_rating')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'decks' => $decks->getCollection()->map(fn ($d) => $this->formatLibraryDeck($d)),
            'pagination' => [
                'current_page' => $decks->currentPage(),
                'last_page' => $decks->lastPage(),
                'total' => $decks->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/library/decks/featured
     */
    public function featured(): JsonResponse
    {
        $decks = Deck::library()
            ->featured()
            ->withCount('cards')
            ->orderByDesc('average_rating')
            ->limit(10)
            ->get();

        return response()->json([
            'decks' => $decks->map(fn ($d) => $this->formatLibraryDeck($d)),
        ]);
    }

    /**
     * GET /api/v1/library/decks/{uuid}
     */
    public function showDeck(string $uuid): JsonResponse
    {
        $deck = Deck::where('uuid', $uuid)
            ->where('visibility', 'library')
            ->withCount('cards')
            ->firstOrFail();

        return response()->json(['deck' => $this->formatLibraryDeck($deck)]);
    }

    /**
     * GET /api/v1/library/decks/{uuid}/cards
     */
    public function deckCards(string $uuid): JsonResponse
    {
        $deck = Deck::where('uuid', $uuid)
            ->where('visibility', 'library')
            ->firstOrFail();

        $cards = $deck->cards()->with('mcqOptions')->orderBy('position')->get();

        return response()->json([
            'cards' => $cards->map(fn ($c) => [
                'id' => $c->id,
                'uuid' => $c->uuid,
                'front_text' => $c->front_text,
                'back_text' => $c->back_text,
                'front_image_url' => $c->front_image_url,
                'back_image_url' => $c->back_image_url,
                'hint' => $c->hint,
                'is_mcq' => $c->is_mcq,
                'mcq_options' => $c->mcqOptions?->map(fn ($o) => [
                    'id' => $o->id,
                    'option_text' => $o->option_text,
                    'is_correct' => $o->is_correct,
                ]),
            ]),
        ]);
    }

    /**
     * POST /api/v1/library/decks/{uuid}/start
     * Start studying a library deck (copies progress for user).
     */
    public function startDeck(Request $request, string $uuid): JsonResponse
    {
        $deck = Deck::where('uuid', $uuid)
            ->where('visibility', 'library')
            ->firstOrFail();

        // Nothing to "copy" — user just starts reviewing the library deck cards
        // The card_progress table tracks per-user progress automatically

        return response()->json([
            'message' => 'Deck started.',
            'deck_uuid' => $deck->uuid,
            'cards_count' => $deck->cards()->count(),
        ]);
    }

    /**
     * POST /api/v1/library/cards/{id}/review
     */
    public function reviewCard(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'quality' => 'required|integer|min:0|max:5',
            'time_spent_ms' => 'nullable|integer|min:0',
        ]);

        $card = Card::findOrFail($id);

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

    private function formatLibraryDeck(Deck $deck): array
    {
        return [
            'id' => $deck->id,
            'uuid' => $deck->uuid,
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
}

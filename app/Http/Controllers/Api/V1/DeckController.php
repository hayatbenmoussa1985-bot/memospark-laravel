<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Deck;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeckController extends Controller
{
    /**
     * GET /api/v1/decks
     * List user's own decks + public decks.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $decks = Deck::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereIn('visibility', ['public', 'library']);
        })
        ->withCount('cards')
        ->orderByDesc('updated_at')
        ->paginate($request->get('per_page', 20));

        return response()->json([
            'decks' => $decks->getCollection()->map(fn ($d) => $this->formatDeck($d)),
            'pagination' => [
                'current_page' => $decks->currentPage(),
                'last_page' => $decks->lastPage(),
                'per_page' => $decks->perPage(),
                'total' => $decks->total(),
            ],
        ]);
    }

    /**
     * POST /api/v1/decks
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'language' => 'sometimes|string|max:50',
            'difficulty' => 'sometimes|in:beginner,intermediate,advanced',
            'visibility' => 'sometimes|in:private,public',
        ]);

        $deck = Deck::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'deck' => $this->formatDeck($deck),
        ], 201);
    }

    /**
     * GET /api/v1/decks/{uuid}
     */
    public function show(string $uuid): JsonResponse
    {
        $deck = Deck::where('uuid', $uuid)
            ->withCount('cards')
            ->firstOrFail();

        return response()->json(['deck' => $this->formatDeck($deck)]);
    }

    /**
     * PUT /api/v1/decks/{uuid}
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $deck = Deck::findByUuidOrFail($uuid);

        if ($request->user()->id !== $deck->user_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'category_id' => 'sometimes|nullable|exists:categories,id',
            'language' => 'sometimes|string|max:50',
            'difficulty' => 'sometimes|in:beginner,intermediate,advanced',
            'visibility' => 'sometimes|in:private,public',
        ]);

        $deck->update($validated);

        return response()->json(['deck' => $this->formatDeck($deck->fresh())]);
    }

    /**
     * DELETE /api/v1/decks/{uuid}
     */
    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $deck = Deck::findByUuidOrFail($uuid);

        if ($request->user()->id !== $deck->user_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $deck->delete(); // Soft delete

        return response()->json(['message' => 'Deck deleted.']);
    }

    /**
     * GET /api/v1/decks/search
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:2']);

        $decks = Deck::where(function ($query) use ($request) {
            $query->where('user_id', $request->user()->id)
                  ->orWhereIn('visibility', ['public', 'library']);
        })
        ->where(function ($query) use ($request) {
            $query->where('title', 'like', "%{$request->q}%")
                  ->orWhere('description', 'like', "%{$request->q}%");
        })
        ->withCount('cards')
        ->limit(20)
        ->get();

        return response()->json([
            'decks' => $decks->map(fn ($d) => $this->formatDeck($d)),
        ]);
    }

    /**
     * GET /api/v1/decks/favorites
     */
    public function favorites(Request $request): JsonResponse
    {
        $decks = $request->user()->favoriteDecks()
            ->withCount('cards')
            ->orderByDesc('deck_favorites.created_at')
            ->get();

        return response()->json([
            'decks' => $decks->map(fn ($d) => $this->formatDeck($d)),
        ]);
    }

    /**
     * POST /api/v1/decks/{uuid}/favorite
     */
    public function favorite(Request $request, string $uuid): JsonResponse
    {
        $deck = Deck::findByUuidOrFail($uuid);
        $request->user()->favoriteDecks()->syncWithoutDetaching([$deck->id]);

        return response()->json(['message' => 'Deck added to favorites.']);
    }

    /**
     * DELETE /api/v1/decks/{uuid}/favorite
     */
    public function unfavorite(Request $request, string $uuid): JsonResponse
    {
        $deck = Deck::findByUuidOrFail($uuid);
        $request->user()->favoriteDecks()->detach($deck->id);

        return response()->json(['message' => 'Deck removed from favorites.']);
    }

    private function formatDeck(Deck $deck): array
    {
        return [
            'id' => $deck->id,
            'uuid' => $deck->uuid,
            'title' => $deck->title,
            'description' => $deck->description,
            'language' => $deck->language,
            'difficulty' => $deck->difficulty?->value,
            'visibility' => $deck->visibility?->value,
            'category_id' => $deck->category_id,
            'cover_image_path' => $deck->cover_image_path,
            'is_featured' => $deck->is_featured,
            'is_ai_generated' => $deck->is_ai_generated,
            'cards_count' => $deck->cards_count ?? 0,
            'average_rating' => $deck->average_rating,
            'ratings_count' => $deck->ratings_count,
            'author' => $deck->user ? [
                'uuid' => $deck->user->uuid,
                'name' => $deck->user->name,
            ] : null,
            'created_at' => $deck->created_at->toIso8601String(),
            'updated_at' => $deck->updated_at->toIso8601String(),
        ];
    }
}

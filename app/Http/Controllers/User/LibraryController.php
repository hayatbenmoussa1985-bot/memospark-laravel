<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Deck;
use App\Services\SM2Service;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function __construct(
        private SM2Service $sm2,
    ) {}

    /**
     * Browse public library.
     */
    public function index(Request $request)
    {
        $query = Deck::library()->with(['user', 'category'])->withCount('cards');

        // Search
        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        // Filter by category
        if ($categoryId = $request->input('category')) {
            $query->where('category_id', $categoryId);
        }

        // Filter by difficulty
        if ($difficulty = $request->input('difficulty')) {
            $query->where('difficulty', $difficulty);
        }

        // Sort
        $sort = $request->input('sort', 'popular');
        $query = match ($sort) {
            'newest' => $query->latest(),
            'popular' => $query->orderByDesc('cards_count'),
            'rating' => $query->orderByDesc('average_rating'),
            default => $query->latest(),
        };

        $decks = $query->paginate(12)->withQueryString();

        $categories = Category::active()->roots()->ordered()->withCount('decks')->get();

        // Featured decks
        $featured = Deck::library()
            ->featured()
            ->with(['user', 'category'])
            ->withCount('cards')
            ->take(4)
            ->get();

        return view('user.library.index', compact('decks', 'categories', 'featured'));
    }

    /**
     * Show a library deck.
     */
    public function show(Deck $deck)
    {
        $deck->load(['user', 'category', 'cards' => fn ($q) => $q->ordered()]);

        $user = auth()->user();

        // Check if user has favorited this deck
        $isFavorited = $user->favoriteDecks()->where('deck_id', $deck->id)->exists();

        // Check if user has started this deck
        $hasStarted = $user->cardProgress()
            ->whereIn('card_id', $deck->cards->pluck('id'))
            ->exists();

        $dueCount = $hasStarted ? $this->sm2->getDueCardsCount($user->id, $deck->id) : 0;

        return view('user.library.show', compact('deck', 'isFavorited', 'hasStarted', 'dueCount'));
    }

    /**
     * Add library deck to favorites.
     */
    public function favorite(Deck $deck)
    {
        auth()->user()->favoriteDecks()->syncWithoutDetaching([$deck->id]);

        return back()->with('success', "Deck added to favorites!");
    }

    /**
     * Remove from favorites.
     */
    public function unfavorite(Deck $deck)
    {
        auth()->user()->favoriteDecks()->detach($deck->id);

        return back()->with('success', "Deck removed from favorites.");
    }

    /**
     * User's favorite decks.
     */
    public function favorites()
    {
        $decks = auth()->user()
            ->favoriteDecks()
            ->with(['user', 'category'])
            ->withCount('cards')
            ->latest('deck_favorites.created_at')
            ->get();

        return view('user.library.favorites', compact('decks'));
    }
}

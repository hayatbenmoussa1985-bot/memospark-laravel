<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Category;
use App\Models\Deck;
use App\Services\SM2Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeckController extends Controller
{
    public function __construct(
        private SM2Service $sm2,
    ) {}

    /**
     * List user's decks.
     */
    public function index()
    {
        $user = auth()->user();

        $decks = $user->decks()
            ->withCount('cards')
            ->latest()
            ->get()
            ->map(function ($deck) use ($user) {
                $deck->due_count = $this->sm2->getDueCardsCount($user->id, $deck->id);
                return $deck;
            });

        return view('user.decks.index', compact('decks'));
    }

    /**
     * Create deck form.
     */
    public function create()
    {
        $categories = Category::active()->ordered()->get();

        return view('user.decks.create', compact('categories'));
    }

    /**
     * Store new deck.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'difficulty' => ['required', 'in:beginner,intermediate,advanced'],
            'visibility' => ['required', 'in:private,public'],
        ]);

        $validated['user_id'] = auth()->id();

        $deck = Deck::create($validated);

        return redirect()
            ->route('user.decks.show', $deck)
            ->with('success', "Deck \"{$deck->title}\" created!");
    }

    /**
     * Show deck with cards.
     */
    public function show(Deck $deck)
    {
        $this->authorize('view', $deck);

        $user = auth()->user();
        $deck->load(['cards' => fn ($q) => $q->ordered(), 'category']);

        $dueCount = $this->sm2->getDueCardsCount($user->id, $deck->id);
        $totalCards = $deck->cards->count();

        // Per-card progress
        $cards = $deck->cards->map(function ($card) use ($user) {
            $card->progress = $card->progressForUser($user->id);
            return $card;
        });

        return view('user.decks.show', compact('deck', 'cards', 'dueCount', 'totalCards'));
    }

    /**
     * Edit deck form.
     */
    public function edit(Deck $deck)
    {
        $this->authorize('update', $deck);
        $categories = Category::active()->ordered()->get();

        return view('user.decks.edit', compact('deck', 'categories'));
    }

    /**
     * Update deck.
     */
    public function update(Request $request, Deck $deck)
    {
        $this->authorize('update', $deck);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'difficulty' => ['required', 'in:beginner,intermediate,advanced'],
            'visibility' => ['required', 'in:private,public'],
        ]);

        $deck->update($validated);

        return redirect()
            ->route('user.decks.show', $deck)
            ->with('success', 'Deck updated!');
    }

    /**
     * Delete deck.
     */
    public function destroy(Deck $deck)
    {
        $this->authorize('delete', $deck);

        $deck->delete();

        return redirect()
            ->route('user.decks.index')
            ->with('success', 'Deck deleted.');
    }

    /**
     * Add card to deck.
     */
    public function storeCard(Request $request, Deck $deck)
    {
        $this->authorize('update', $deck);

        $validated = $request->validate([
            'front_text' => ['required', 'string'],
            'back_text' => ['required', 'string'],
            'hint' => ['nullable', 'string', 'max:500'],
            'front_image_path' => ['nullable', 'string'],
            'back_image_path' => ['nullable', 'string'],
        ]);

        $validated['position'] = $deck->cards()->max('position') + 1;

        $card = $deck->cards()->create($validated);
        $deck->updateCardsCount();

        return redirect()
            ->route('user.decks.show', $deck)
            ->with('success', 'Card added!');
    }

    /**
     * Delete card from deck.
     */
    public function destroyCard(Deck $deck, Card $card)
    {
        $this->authorize('update', $deck);

        $card->delete();
        $deck->updateCardsCount();

        return redirect()
            ->route('user.decks.show', $deck)
            ->with('success', 'Card removed.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Deck;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeckController extends Controller
{
    /**
     * List all decks with search and filter.
     */
    public function index(Request $request)
    {
        $query = Deck::with(['user', 'category'])->withCount('cards');

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by visibility
        if ($visibility = $request->input('visibility')) {
            $query->where('visibility', $visibility);
        }

        // Filter by category
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Filter featured only
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        $decks = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::active()->ordered()->get();

        return view('admin.decks.index', compact('decks', 'categories'));
    }

    /**
     * Show deck detail with cards.
     */
    public function show(Deck $deck)
    {
        $deck->load(['user', 'category', 'cards' => fn ($q) => $q->ordered()]);

        return view('admin.decks.show', compact('deck'));
    }

    /**
     * Edit deck form.
     */
    public function edit(Deck $deck)
    {
        $categories = Category::active()->ordered()->get();

        return view('admin.decks.edit', compact('deck', 'categories'));
    }

    /**
     * Update deck.
     */
    public function update(Request $request, Deck $deck)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'visibility' => ['required', Rule::in(['private', 'public', 'library'])],
            'difficulty' => ['required', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'is_featured' => ['boolean'],
        ]);

        $oldValues = $deck->only(['title', 'visibility', 'is_featured']);

        $deck->update($validated);

        AuditLog::record(
            action: 'deck_updated',
            targetType: 'deck',
            targetId: $deck->id,
            oldValues: $oldValues,
            newValues: $validated,
        );

        return redirect()
            ->route('admin.decks.show', $deck)
            ->with('success', "Deck \"{$deck->title}\" updated successfully.");
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(Deck $deck)
    {
        $deck->update(['is_featured' => !$deck->is_featured]);

        AuditLog::record(
            action: $deck->is_featured ? 'deck_featured' : 'deck_unfeatured',
            targetType: 'deck',
            targetId: $deck->id,
        );

        $status = $deck->is_featured ? 'featured' : 'unfeatured';

        return back()->with('success', "Deck \"{$deck->title}\" is now {$status}.");
    }

    /**
     * Soft delete a deck.
     */
    public function destroy(Deck $deck)
    {
        AuditLog::record(
            action: 'deck_deleted',
            targetType: 'deck',
            targetId: $deck->id,
            oldValues: ['title' => $deck->title, 'user' => $deck->user?->name],
        );

        $deck->delete();

        return redirect()
            ->route('admin.decks.index')
            ->with('success', "Deck \"{$deck->title}\" has been deleted.");
    }
}

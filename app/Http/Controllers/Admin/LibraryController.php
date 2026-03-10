<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Deck;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    /**
     * Library management overview: categories + library decks.
     */
    public function index(Request $request)
    {
        $categories = Category::withCount('decks')
            ->with('parentCategory')
            ->ordered()
            ->get();

        $libraryDecks = Deck::library()
            ->with(['user', 'category'])
            ->withCount('cards')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.library.index', compact('categories', 'libraryDecks'));
    }

    /**
     * Create category form.
     */
    public function createCategory()
    {
        $parentCategories = Category::roots()->ordered()->get();

        return view('admin.library.create-category', compact('parentCategories'));
    }

    /**
     * Store new category.
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:100', 'unique:categories,slug'],
            'icon' => ['nullable', 'string', 'max:50'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'sort_order' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $category = Category::create($validated);

        AuditLog::record(
            action: 'category_created',
            targetType: 'category',
            targetId: $category->id,
            newValues: $validated,
        );

        return redirect()
            ->route('admin.library.index')
            ->with('success', "Category \"{$category->slug}\" created successfully.");
    }

    /**
     * Edit category.
     */
    public function editCategory(Category $category)
    {
        $parentCategories = Category::roots()
            ->where('id', '!=', $category->id)
            ->ordered()
            ->get();

        return view('admin.library.edit-category', compact('category', 'parentCategories'));
    }

    /**
     * Update category.
     */
    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:100', 'unique:categories,slug,' . $category->id],
            'icon' => ['nullable', 'string', 'max:50'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'sort_order' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        // Prevent self-referencing
        if (isset($validated['parent_id']) && $validated['parent_id'] == $category->id) {
            return back()->with('error', 'A category cannot be its own parent.');
        }

        $oldValues = $category->only(['slug', 'icon', 'parent_id', 'sort_order', 'is_active']);

        $category->update($validated);

        AuditLog::record(
            action: 'category_updated',
            targetType: 'category',
            targetId: $category->id,
            oldValues: $oldValues,
            newValues: $validated,
        );

        return redirect()
            ->route('admin.library.index')
            ->with('success', "Category \"{$category->slug}\" updated.");
    }

    /**
     * Delete category.
     */
    public function destroyCategory(Category $category)
    {
        // Prevent deletion if category has decks
        if ($category->decks()->exists()) {
            return back()->with('error', 'Cannot delete category with associated decks. Reassign them first.');
        }

        AuditLog::record(
            action: 'category_deleted',
            targetType: 'category',
            targetId: $category->id,
            oldValues: ['slug' => $category->slug],
        );

        $category->delete();

        return redirect()
            ->route('admin.library.index')
            ->with('success', 'Category deleted.');
    }
}

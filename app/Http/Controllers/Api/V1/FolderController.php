<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Deck;
use App\Models\Folder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    /**
     * GET /library/tree
     * Get the user's library tree (folders with nested decks).
     */
    public function tree(Request $request): JsonResponse
    {
        $user = $request->user();

        $folders = Folder::where('user_id', $user->id)
            ->whereNull('parent_id')
            ->with(['children', 'decks:id,uuid,title,cover_image_path,cards_count'])
            ->orderBy('sort_order')
            ->get();

        $unorganizedDecks = $user->decks()
            ->whereDoesntHave('folders', function ($q) use ($user) {
                $q->where('deck_folder.user_id', $user->id);
            })
            ->select('id', 'uuid', 'title', 'cover_image_path', 'cards_count')
            ->get();

        return response()->json([
            'folders' => $folders->map(fn ($f) => $this->formatFolder($f)),
            'unorganized_decks' => $unorganizedDecks->map(fn ($d) => [
                'id' => $d->id,
                'uuid' => $d->uuid,
                'title' => $d->title,
                'cover_image_path' => $d->cover_image_path,
                'cards_count' => $d->cards_count ?? 0,
            ]),
        ]);
    }

    /**
     * GET /library/assignments
     * Get deck-to-folder assignments for the current user.
     */
    public function assignments(Request $request): JsonResponse
    {
        $user = $request->user();

        $assignments = \DB::table('deck_folder')
            ->where('user_id', $user->id)
            ->select('deck_id', 'folder_id', 'sort_order')
            ->get();

        return response()->json([
            'data' => $assignments,
        ]);
    }

    /**
     * GET /library/folders
     */
    public function index(Request $request): JsonResponse
    {
        $folders = Folder::where('user_id', $request->user()->id)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => $folders->map(fn ($f) => $this->formatFolder($f)),
        ]);
    }

    /**
     * POST /library/folders
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|integer|exists:folders,id',
        ]);

        $maxSort = Folder::where('user_id', $request->user()->id)->max('sort_order') ?? 0;

        $folder = Folder::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'color' => $validated['color'] ?? '#6366f1',
            'icon' => $validated['icon'] ?? "\u{1F4DA}",
            'parent_id' => $validated['parent_id'] ?? null,
            'sort_order' => $maxSort + 1,
        ]);

        return response()->json($this->formatFolder($folder), 201);
    }

    /**
     * PATCH /library/folders/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $folder = Folder::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|integer|exists:folders,id',
            'sort_order' => 'nullable|integer',
        ]);

        $folder->update($validated);

        return response()->json($this->formatFolder($folder->fresh()));
    }

    /**
     * DELETE /library/folders/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $folder = Folder::where('user_id', $request->user()->id)
            ->findOrFail($id);

        // Detach all decks from this folder
        $folder->decks()->detach();
        $folder->delete();

        return response()->json(['message' => 'Folder deleted.']);
    }

    /**
     * POST /library/organize
     * Move a deck into a folder (or remove from folder).
     */
    public function organize(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'deck_id' => 'required|integer|exists:decks,id',
            'folder_id' => 'nullable|integer|exists:folders,id',
            'sort_order' => 'nullable|integer',
        ]);

        $userId = $request->user()->id;

        // Remove from current folder
        \DB::table('deck_folder')
            ->where('deck_id', $validated['deck_id'])
            ->where('user_id', $userId)
            ->delete();

        // Add to new folder if specified
        if ($validated['folder_id']) {
            \DB::table('deck_folder')->insert([
                'deck_id' => $validated['deck_id'],
                'folder_id' => $validated['folder_id'],
                'user_id' => $userId,
                'sort_order' => $validated['sort_order'] ?? 0,
            ]);
        }

        return response()->json(['message' => 'Deck organized.']);
    }

    private function formatFolder(Folder $folder): array
    {
        return [
            'id' => $folder->id,
            'name' => $folder->name,
            'color' => $folder->color,
            'icon' => $folder->icon,
            'parent_id' => $folder->parent_id,
            'sort_order' => $folder->sort_order,
            'children' => $folder->relationLoaded('children')
                ? $folder->children->map(fn ($c) => $this->formatFolder($c))
                : [],
            'decks' => $folder->relationLoaded('decks')
                ? $folder->decks->map(fn ($d) => [
                    'id' => $d->id,
                    'uuid' => $d->uuid,
                    'title' => $d->title,
                    'cover_image_path' => $d->cover_image_path,
                    'cards_count' => $d->cards_count ?? 0,
                ])
                : [],
        ];
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BadgeController extends Controller
{
    /**
     * GET /badges
     */
    public function index(): JsonResponse
    {
        $badges = Badge::all();

        return response()->json([
            'data' => $badges->map(fn ($b) => $this->formatBadge($b)),
        ]);
    }

    /**
     * GET /badges/mine
     * GET /badges/earned (alias)
     */
    public function mine(Request $request): JsonResponse
    {
        $badges = $request->user()->badges()->get();

        return response()->json([
            'data' => $badges->map(fn ($b) => [
                ...$this->formatBadge($b),
                'awarded_at' => $b->pivot->awarded_at,
                'awarded_by' => $b->pivot->awarded_by,
            ]),
        ]);
    }

    /**
     * GET /badges/{slug}
     */
    public function show(string $slug): JsonResponse
    {
        $badge = Badge::where('slug', $slug)->firstOrFail();

        return response()->json($this->formatBadge($badge));
    }

    /**
     * POST /badges/assign
     */
    public function assign(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'badge_id' => 'nullable|integer|exists:badges,id',
            'badge_slug' => 'nullable|string|exists:badges,slug',
        ]);

        $badgeId = $validated['badge_id']
            ?? Badge::where('slug', $validated['badge_slug'] ?? '')->value('id');

        if (!$badgeId) {
            return response()->json(['message' => 'Badge not found.'], 404);
        }

        $exists = DB::table('user_badges')
            ->where('user_id', $validated['user_id'])
            ->where('badge_id', $badgeId)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Badge already awarded.'], 409);
        }

        DB::table('user_badges')->insert([
            'user_id' => $validated['user_id'],
            'badge_id' => $badgeId,
            'awarded_by' => $request->user()->id,
            'awarded_at' => now(),
        ]);

        return response()->json(['message' => 'Badge assigned.'], 201);
    }

    private function formatBadge(Badge $badge): array
    {
        return [
            'id' => $badge->id,
            'slug' => $badge->slug,
            'name' => $badge->translatedName(),
            'description' => $badge->description,
            'icon' => $badge->icon,
            'color' => $badge->color,
            'criteria' => $badge->criteria,
        ];
    }
}

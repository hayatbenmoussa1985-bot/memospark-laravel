<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    /**
     * GET /api/v1/badges
     * List all available badges.
     */
    public function index(): JsonResponse
    {
        $badges = Badge::all();

        return response()->json([
            'badges' => $badges->map(fn ($b) => [
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
     * GET /api/v1/badges/mine
     * List badges earned by the authenticated user.
     */
    public function mine(Request $request): JsonResponse
    {
        $badges = $request->user()->badges()->get();

        return response()->json([
            'badges' => $badges->map(fn ($b) => [
                'id' => $b->id,
                'slug' => $b->slug,
                'name' => $b->translatedName(),
                'icon' => $b->icon,
                'color' => $b->color,
                'awarded_at' => $b->pivot->awarded_at,
            ]),
        ]);
    }
}

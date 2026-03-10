<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CardProgress;
use App\Models\StudySession;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoints for managing children (called by parent or child).
 * Routes: /children/{childId}/*
 */
class ChildrenController extends Controller
{
    /**
     * GET /children/{childId}
     * Get child profile. Accessible by parent or the child themselves.
     */
    public function show(Request $request, string $childId): JsonResponse
    {
        $child = $this->resolveChild($childId);

        // Verify access: parent of this child, or the child themselves, or admin
        $this->authorizeChildAccess($request->user(), $child);

        return response()->json($this->formatChild($child));
    }

    /**
     * GET /children/{childId}/stats
     * Get study statistics for a child.
     */
    public function stats(Request $request, string $childId): JsonResponse
    {
        $child = $this->resolveChild($childId);
        $this->authorizeChildAccess($request->user(), $child);

        $totalReviews = CardProgress::where('user_id', $child->id)->sum('total_reviews');
        $correctReviews = CardProgress::where('user_id', $child->id)->sum('correct_reviews');

        $recentSessions = StudySession::where('user_id', $child->id)
            ->orderByDesc('started_at')
            ->limit(10)
            ->get();

        $weeklyStats = StudySession::where('user_id', $child->id)
            ->where('started_at', '>=', now()->subDays(7))
            ->selectRaw('COUNT(*) as sessions, COALESCE(SUM(cards_reviewed),0) as cards, COALESCE(SUM(correct_count),0) as correct, COALESCE(SUM(duration_seconds),0) as time')
            ->first();

        // Streak calculation
        $streak = $this->calculateStreak($child->id);

        return response()->json([
            'child' => [
                'id' => $child->id,
                'uuid' => $child->uuid,
                'name' => $child->name,
            ],
            'stats' => [
                'total_reviews' => (int) $totalReviews,
                'correct_reviews' => (int) $correctReviews,
                'accuracy_rate' => $totalReviews > 0
                    ? round(($correctReviews / $totalReviews) * 100, 1)
                    : 0,
                'streak_days' => $streak,
            ],
            'weekly' => [
                'sessions' => (int) ($weeklyStats->sessions ?? 0),
                'cards_reviewed' => (int) ($weeklyStats->cards ?? 0),
                'correct_count' => (int) ($weeklyStats->correct ?? 0),
                'study_time_seconds' => (int) ($weeklyStats->time ?? 0),
            ],
            'recent_sessions' => $recentSessions->map(fn ($s) => [
                'id' => $s->id,
                'deck_id' => $s->deck_id,
                'cards_reviewed' => $s->cards_reviewed,
                'correct_count' => $s->correct_count,
                'duration_seconds' => $s->duration_seconds,
                'started_at' => $s->started_at->toIso8601String(),
            ]),
        ]);
    }

    /**
     * GET /children/{childId}/badges
     */
    public function badges(Request $request, string $childId): JsonResponse
    {
        $child = $this->resolveChild($childId);
        $this->authorizeChildAccess($request->user(), $child);

        $badges = $child->badges()->get();

        return response()->json([
            'badges' => $badges->map(fn ($b) => [
                'id' => $b->id,
                'slug' => $b->slug,
                'name' => $b->name,
                'description' => $b->description,
                'icon' => $b->icon,
                'color' => $b->color,
                'awarded_at' => $b->pivot->awarded_at,
            ]),
        ]);
    }

    /**
     * GET /children/{childId}/activities
     */
    public function activities(Request $request, string $childId): JsonResponse
    {
        $child = $this->resolveChild($childId);
        $this->authorizeChildAccess($request->user(), $child);

        $activities = ActivityLog::where('user_id', $child->id)
            ->orderByDesc('created_at')
            ->limit($request->get('limit', 20))
            ->get();

        return response()->json([
            'data' => $activities->map(fn ($a) => [
                'id' => $a->id,
                'activity_type' => $a->activity_type,
                'deck_id' => $a->deck_id,
                'metadata' => $a->metadata,
                'duration_minutes' => $a->duration_minutes,
                'cards_reviewed' => $a->cards_reviewed,
                'success_rate' => $a->success_rate,
                'created_at' => $a->created_at->toIso8601String(),
            ]),
        ]);
    }

    /**
     * PUT/PATCH /children/{childId}
     * Update child profile (name, school_level, date_of_birth, avatar).
     */
    public function update(Request $request, string $childId): JsonResponse
    {
        $child = $this->resolveChild($childId);
        $this->authorizeChildAccess($request->user(), $child);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'school_level' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'avatar_path' => 'nullable|string|max:500',
            'locale' => 'nullable|string|max:5',
        ]);

        $child->update($validated);

        return response()->json($this->formatChild($child->fresh()));
    }

    // ──────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────

    /**
     * Resolve a child by UUID or ID.
     */
    private function resolveChild(string $childId): User
    {
        // Try UUID first, then numeric ID
        if (strlen($childId) === 36 || str_contains($childId, '-')) {
            return User::where('uuid', $childId)->firstOrFail();
        }

        return User::findOrFail($childId);
    }

    /**
     * Verify the current user can access this child's data.
     */
    private function authorizeChildAccess(User $currentUser, User $child): void
    {
        // Admin / super_admin can access any child
        if ($currentUser->isSuperAdmin() || $currentUser->isAdmin()) {
            return;
        }

        // The child themselves
        if ($currentUser->id === $child->id) {
            return;
        }

        // Parent of this child
        if ($currentUser->isParent()) {
            $isParent = $currentUser->children()->where('users.id', $child->id)->exists();
            if ($isParent) {
                return;
            }
        }

        abort(403, 'You do not have access to this child.');
    }

    private function calculateStreak(int $userId): int
    {
        $dates = StudySession::where('user_id', $userId)
            ->where('started_at', '>=', now()->subDays(365))
            ->selectRaw('DATE(started_at) as study_date')
            ->groupBy('study_date')
            ->orderByDesc('study_date')
            ->pluck('study_date')
            ->map(fn ($d) => \Carbon\Carbon::parse($d));

        if ($dates->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $expected = now()->startOfDay();

        // If no session today, check if there was one yesterday
        if (!$dates->first()->isSameDay($expected)) {
            $expected = $expected->subDay();
        }

        foreach ($dates as $date) {
            if ($date->isSameDay($expected)) {
                $streak++;
                $expected = $expected->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    private function formatChild(User $child): array
    {
        return [
            'id' => $child->id,
            'uuid' => $child->uuid,
            'name' => $child->name,
            'email' => $child->email,
            'avatar_path' => $child->avatar_path,
            'date_of_birth' => $child->date_of_birth?->toDateString(),
            'school_level' => $child->school_level,
            'role' => $child->role->value,
            'locale' => $child->locale,
            'is_active' => $child->is_active,
            'created_at' => $child->created_at->toIso8601String(),
        ];
    }
}

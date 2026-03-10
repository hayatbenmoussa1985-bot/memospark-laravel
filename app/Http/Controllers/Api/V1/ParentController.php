<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Badge;
use App\Models\CardProgress;
use App\Models\Notification;
use App\Models\RevisionPlan;
use App\Models\StudySession;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    /**
     * GET /parent/children
     * GET /parent/linked-children
     */
    public function children(Request $request): JsonResponse
    {
        $children = $request->user()->children()->get();

        return response()->json([
            'data' => $children->map(fn ($child) => [
                'id' => $child->id,
                'uuid' => $child->uuid,
                'name' => $child->name,
                'email' => $child->email,
                'avatar_path' => $child->avatar_path,
                'date_of_birth' => $child->date_of_birth?->toDateString(),
                'school_level' => $child->school_level,
                'role' => $child->role->value,
                'relationship' => $child->pivot->relationship,
                'created_at' => $child->created_at->toIso8601String(),
            ]),
        ]);
    }

    /**
     * GET /parents/{parentId}
     * Get parent profile.
     */
    public function show(Request $request, string $parentId): JsonResponse
    {
        $parent = $this->resolveUser($parentId);

        if ($request->user()->id !== $parent->id && !$request->user()->isSuperAdmin()) {
            abort(403);
        }

        return response()->json([
            'id' => $parent->id,
            'uuid' => $parent->uuid,
            'name' => $parent->name,
            'email' => $parent->email,
            'avatar_path' => $parent->avatar_path,
            'role' => $parent->role->value,
            'created_at' => $parent->created_at->toIso8601String(),
        ]);
    }

    /**
     * GET /parents/{parentId}/dashboard
     * Get dashboard stats for parent.
     */
    public function dashboard(Request $request, string $parentId): JsonResponse
    {
        $parent = $this->resolveUser($parentId);

        if ($request->user()->id !== $parent->id && !$request->user()->isSuperAdmin()) {
            abort(403);
        }

        $children = $parent->children()->get();

        $childrenStats = $children->map(function ($child) {
            $weeklyCards = StudySession::where('user_id', $child->id)
                ->where('started_at', '>=', now()->subDays(7))
                ->sum('cards_reviewed');

            $weeklyTime = StudySession::where('user_id', $child->id)
                ->where('started_at', '>=', now()->subDays(7))
                ->sum('duration_seconds');

            $lastActivity = ActivityLog::where('user_id', $child->id)
                ->orderByDesc('created_at')
                ->first();

            return [
                'id' => $child->id,
                'uuid' => $child->uuid,
                'name' => $child->name,
                'avatar_path' => $child->avatar_path,
                'weekly_cards' => (int) $weeklyCards,
                'weekly_time_seconds' => (int) $weeklyTime,
                'last_activity_at' => $lastActivity?->created_at?->toIso8601String(),
                'badges_count' => $child->badges()->count(),
            ];
        });

        $totalPlans = RevisionPlan::where('parent_id', $parent->id)->count();
        $activePlans = RevisionPlan::where('parent_id', $parent->id)
            ->where('status', 'active')
            ->count();

        return response()->json([
            'children' => $childrenStats,
            'plans' => [
                'total' => $totalPlans,
                'active' => $activePlans,
            ],
        ]);
    }

    /**
     * GET /parents/{parentId}/children
     * Get parent's children (alternative route).
     */
    public function parentChildren(Request $request, string $parentId): JsonResponse
    {
        $parent = $this->resolveUser($parentId);

        if ($request->user()->id !== $parent->id && !$request->user()->isSuperAdmin()) {
            abort(403);
        }

        $children = $parent->children()->get();

        return response()->json([
            'data' => $children->map(fn ($child) => [
                'id' => $child->id,
                'uuid' => $child->uuid,
                'name' => $child->name,
                'email' => $child->email,
                'avatar_path' => $child->avatar_path,
                'date_of_birth' => $child->date_of_birth?->toDateString(),
                'school_level' => $child->school_level,
                'role' => $child->role->value,
                'relationship' => $child->pivot->relationship ?? 'parent',
                'created_at' => $child->created_at->toIso8601String(),
            ]),
        ]);
    }

    /**
     * POST /parent-child-links
     * Link a child to a parent.
     */
    public function linkChild(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => 'required',
            'child_id' => 'required',
            'relationship' => 'nullable|in:parent,guardian,tutor',
        ]);

        $parent = $this->resolveUser($validated['parent_id']);
        $child = $this->resolveUser($validated['child_id']);

        // Check this user can link
        if ($request->user()->id !== $parent->id && !$request->user()->isSuperAdmin()) {
            abort(403);
        }

        // Check child isn't already linked
        if ($parent->children()->where('users.id', $child->id)->exists()) {
            return response()->json(['message' => 'Child already linked.'], 409);
        }

        $parent->children()->attach($child->id, [
            'relationship' => $validated['relationship'] ?? 'parent',
            'created_at' => now(),
        ]);

        return response()->json(['message' => 'Child linked successfully.'], 201);
    }

    /**
     * DELETE /parent-child-links/{parentId}/{childId}
     */
    public function unlinkChild(Request $request, string $parentId, string $childId): JsonResponse
    {
        $parent = $this->resolveUser($parentId);
        $child = $this->resolveUser($childId);

        if ($request->user()->id !== $parent->id && !$request->user()->isSuperAdmin()) {
            abort(403);
        }

        $parent->children()->detach($child->id);

        return response()->json(['message' => 'Child unlinked.']);
    }

    /**
     * GET /parent/children/{id}/stats
     */
    public function childStats(Request $request, int $id): JsonResponse
    {
        $child = $request->user()->children()->where('users.id', $id)->firstOrFail();

        $totalReviews = CardProgress::where('user_id', $child->id)->sum('total_reviews');
        $correctReviews = CardProgress::where('user_id', $child->id)->sum('correct_reviews');

        $recentSessions = StudySession::where('user_id', $child->id)
            ->orderByDesc('started_at')
            ->limit(10)
            ->get();

        $recentActivities = ActivityLog::where('user_id', $child->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $weeklyStats = StudySession::where('user_id', $child->id)
            ->where('started_at', '>=', now()->subDays(7))
            ->selectRaw('COUNT(*) as sessions, COALESCE(SUM(cards_reviewed),0) as cards, COALESCE(SUM(correct_count),0) as correct, COALESCE(SUM(duration_seconds),0) as time')
            ->first();

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
            'recent_activities' => $recentActivities->map(fn ($a) => [
                'type' => $a->activity_type,
                'deck_id' => $a->deck_id,
                'cards_reviewed' => $a->cards_reviewed,
                'success_rate' => $a->success_rate,
                'created_at' => $a->created_at->toIso8601String(),
            ]),
        ]);
    }

    /**
     * POST /parent/revision-plans
     * POST /revision-plans
     */
    public function storeRevisionPlan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'child_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'daily_goal_cards' => 'sometimes|integer|min:1|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'deck_ids' => 'required|array|min:1',
            'deck_ids.*' => 'integer|exists:decks,id',
        ]);

        $request->user()->children()->where('users.id', $validated['child_id'])->firstOrFail();

        $plan = RevisionPlan::create([
            'parent_id' => $request->user()->id,
            'child_id' => $validated['child_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'daily_goal_cards' => $validated['daily_goal_cards'] ?? 20,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
        ]);

        $plan->decks()->attach($validated['deck_ids']);

        return response()->json([
            'plan' => $this->formatPlan($plan->load('decks')),
        ], 201);
    }

    /**
     * GET /parent/revision-plans
     */
    public function revisionPlans(Request $request): JsonResponse
    {
        $plans = RevisionPlan::where('parent_id', $request->user()->id)
            ->with(['childUser:id,uuid,name', 'decks:id,uuid,title'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $plans->map(fn ($p) => $this->formatPlan($p)),
        ]);
    }

    /**
     * GET /revision-plans/child/{childId}
     */
    public function childRevisionPlans(Request $request, string $childId): JsonResponse
    {
        $child = $this->resolveUser($childId);

        $plans = RevisionPlan::where('child_id', $child->id)
            ->with(['decks:id,uuid,title'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $plans->map(fn ($p) => $this->formatPlan($p)),
        ]);
    }

    /**
     * PATCH /revision-plans/{planId}
     */
    public function updateRevisionPlan(Request $request, int $planId): JsonResponse
    {
        $plan = RevisionPlan::where('parent_id', $request->user()->id)
            ->findOrFail($planId);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'daily_goal_cards' => 'sometimes|integer|min:1|max:100',
            'status' => 'sometimes|in:active,paused,completed',
            'end_date' => 'nullable|date',
        ]);

        $plan->update($validated);

        return response()->json([
            'plan' => $this->formatPlan($plan->fresh()->load('decks')),
        ]);
    }

    /**
     * POST /feedbacks
     */
    public function sendFeedback(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'child_id' => 'required|integer',
            'type' => 'nullable|string|max:50',
            'message' => 'required|string|max:1000',
            'emoji' => 'nullable|string|max:10',
        ]);

        // Verify parent-child relationship
        $request->user()->children()->where('users.id', $validated['child_id'])->firstOrFail();

        // Create as a notification for the child
        $notification = Notification::create([
            'user_id' => $validated['child_id'],
            'title' => $validated['type'] === 'encouragement'
                ? ($validated['emoji'] ?? '💪') . ' Encouragement'
                : 'Message de ' . $request->user()->name,
            'message' => $validated['message'],
            'type' => 'feedback',
            'data' => [
                'from_parent_id' => $request->user()->id,
                'from_parent_name' => $request->user()->name,
                'feedback_type' => $validated['type'] ?? 'message',
                'emoji' => $validated['emoji'] ?? null,
            ],
        ]);

        return response()->json(['message' => 'Feedback sent.'], 201);
    }

    /**
     * GET /feedbacks/user/{userId}
     */
    public function userFeedback(Request $request, string $userId): JsonResponse
    {
        $user = $this->resolveUser($userId);

        $feedbacks = Notification::where('user_id', $user->id)
            ->where('type', 'feedback')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json([
            'data' => $feedbacks->map(fn ($f) => [
                'id' => $f->id,
                'title' => $f->title,
                'message' => $f->message,
                'type' => $f->data['feedback_type'] ?? 'message',
                'emoji' => $f->data['emoji'] ?? null,
                'from' => $f->data['from_parent_name'] ?? null,
                'read_at' => $f->read_at?->toIso8601String(),
                'created_at' => $f->created_at->toIso8601String(),
            ]),
        ]);
    }

    /**
     * POST /badges (award badge to child)
     */
    public function awardBadge(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'child_id' => 'required|integer',
            'badge_id' => 'required|integer|exists:badges,id',
        ]);

        $request->user()->children()->where('users.id', $validated['child_id'])->firstOrFail();
        $badge = Badge::findOrFail($validated['badge_id']);

        // Check if already awarded
        $exists = \DB::table('user_badges')
            ->where('user_id', $validated['child_id'])
            ->where('badge_id', $validated['badge_id'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Badge already awarded.'], 409);
        }

        \DB::table('user_badges')->insert([
            'user_id' => $validated['child_id'],
            'badge_id' => $validated['badge_id'],
            'awarded_by' => $request->user()->id,
            'awarded_at' => now(),
        ]);

        return response()->json(['message' => 'Badge awarded.'], 201);
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    private function resolveUser(string $id): User
    {
        if (strlen($id) === 36 || str_contains($id, '-')) {
            return User::where('uuid', $id)->firstOrFail();
        }
        return User::findOrFail($id);
    }

    private function formatPlan(RevisionPlan $plan): array
    {
        return [
            'id' => $plan->id,
            'title' => $plan->title,
            'description' => $plan->description,
            'daily_goal_cards' => $plan->daily_goal_cards,
            'start_date' => $plan->start_date->toDateString(),
            'end_date' => $plan->end_date?->toDateString(),
            'status' => $plan->status,
            'child' => $plan->childUser ? [
                'id' => $plan->childUser->id,
                'uuid' => $plan->childUser->uuid,
                'name' => $plan->childUser->name,
            ] : null,
            'decks' => $plan->decks->map(fn ($d) => [
                'id' => $d->id,
                'uuid' => $d->uuid,
                'title' => $d->title,
            ]),
            'created_at' => $plan->created_at->toIso8601String(),
        ];
    }
}

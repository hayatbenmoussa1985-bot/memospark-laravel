<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CardProgress;
use App\Models\RevisionPlan;
use App\Models\StudySession;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    /**
     * GET /api/v1/parent/children
     */
    public function children(Request $request): JsonResponse
    {
        $children = $request->user()->children()->get();

        return response()->json([
            'children' => $children->map(fn ($child) => [
                'id' => $child->id,
                'uuid' => $child->uuid,
                'name' => $child->name,
                'avatar_path' => $child->avatar_path,
                'date_of_birth' => $child->date_of_birth?->toDateString(),
                'school_level' => $child->school_level,
                'relationship' => $child->pivot->relationship,
            ]),
        ]);
    }

    /**
     * GET /api/v1/parent/children/{id}/stats
     */
    public function childStats(Request $request, int $id): JsonResponse
    {
        // Verify parent-child relationship
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

        // Weekly summary
        $weeklyStats = StudySession::where('user_id', $child->id)
            ->where('started_at', '>=', now()->subDays(7))
            ->selectRaw('COUNT(*) as sessions, SUM(cards_reviewed) as cards, SUM(correct_count) as correct, SUM(duration_seconds) as time')
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
     * POST /api/v1/parent/revision-plans
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

        // Verify parent-child relationship
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
     * GET /api/v1/parent/revision-plans
     */
    public function revisionPlans(Request $request): JsonResponse
    {
        $plans = RevisionPlan::where('parent_id', $request->user()->id)
            ->with(['childUser:id,uuid,name', 'decks:id,uuid,title'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'plans' => $plans->map(fn ($p) => $this->formatPlan($p)),
        ]);
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

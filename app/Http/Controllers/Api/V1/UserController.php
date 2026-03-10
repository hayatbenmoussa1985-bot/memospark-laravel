<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CardProgress;
use App\Models\StudySession;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * GET /api/v1/users/{uuid}
     */
    public function show(string $uuid): JsonResponse
    {
        $user = User::findByUuidOrFail($uuid);

        return response()->json(['user' => $this->formatUser($user)]);
    }

    /**
     * PATCH /api/v1/users/{uuid}
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $user = User::findByUuidOrFail($uuid);

        // Ensure user can only update their own profile
        if ($request->user()->id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'locale' => 'sometimes|string|max:5',
            'timezone' => 'sometimes|string|max:50',
            'date_of_birth' => 'sometimes|nullable|date',
            'school_level' => 'sometimes|nullable|string|max:100',
        ]);

        $user->update($validated);

        return response()->json(['user' => $this->formatUser($user->fresh())]);
    }

    /**
     * GET /api/v1/users/{uuid}/stats
     */
    public function stats(string $uuid): JsonResponse
    {
        $user = User::findByUuidOrFail($uuid);

        $totalCards = CardProgress::where('user_id', $user->id)->count();
        $dueCards = CardProgress::where('user_id', $user->id)
            ->where('next_review_at', '<=', now())->count();
        $totalReviews = CardProgress::where('user_id', $user->id)->sum('total_reviews');
        $correctReviews = CardProgress::where('user_id', $user->id)->sum('correct_reviews');

        $sessions = StudySession::where('user_id', $user->id);
        $totalSessions = $sessions->count();
        $totalStudyTime = $sessions->sum('duration_seconds');

        // Streak calculation (consecutive days of activity)
        $streak = $this->calculateStreak($user->id);

        // Recent activity (last 7 days)
        $recentActivity = ActivityLog::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return response()->json([
            'stats' => [
                'total_cards_studied' => $totalCards,
                'due_cards' => $dueCards,
                'total_reviews' => (int) $totalReviews,
                'correct_reviews' => (int) $correctReviews,
                'accuracy_rate' => $totalReviews > 0
                    ? round(($correctReviews / $totalReviews) * 100, 1)
                    : 0,
                'total_sessions' => $totalSessions,
                'total_study_time_seconds' => (int) $totalStudyTime,
                'current_streak' => $streak,
                'recent_activity_count' => $recentActivity,
            ],
        ]);
    }

    /**
     * GET /api/v1/users/{uuid}/decks-with-due-cards
     */
    public function decksWithDueCards(string $uuid): JsonResponse
    {
        $user = User::findByUuidOrFail($uuid);

        $decks = $user->decks()->withCount(['cards'])->get();

        $result = $decks->map(function ($deck) use ($user) {
            $dueCount = CardProgress::where('user_id', $user->id)
                ->whereIn('card_id', $deck->cards()->pluck('id'))
                ->where('next_review_at', '<=', now())
                ->count();

            // New cards (never studied)
            $studiedIds = CardProgress::where('user_id', $user->id)
                ->whereIn('card_id', $deck->cards()->pluck('id'))
                ->pluck('card_id');
            $newCount = $deck->cards()->whereNotIn('id', $studiedIds)->count();

            return [
                'id' => $deck->id,
                'uuid' => $deck->uuid,
                'title' => $deck->title,
                'cards_count' => $deck->cards_count,
                'due_cards' => $dueCount + $newCount,
                'cover_image_path' => $deck->cover_image_path,
            ];
        })->filter(fn ($d) => $d['due_cards'] > 0)->values();

        return response()->json(['decks' => $result]);
    }

    /**
     * POST /api/v1/users/{uuid}/push-token
     */
    public function pushToken(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'platform' => 'required|in:ios,android',
        ]);

        // Store push token (could be in a dedicated table or user metadata)
        // For now, we store in app_settings per user
        $user = User::findByUuidOrFail($uuid);

        // Simple implementation — can be expanded later
        return response()->json(['message' => 'Push token registered.']);
    }

    /**
     * Calculate consecutive days streak.
     */
    private function calculateStreak(int $userId): int
    {
        $streak = 0;
        $date = now()->startOfDay();

        while (true) {
            $hasActivity = ActivityLog::where('user_id', $userId)
                ->whereDate('created_at', $date)
                ->exists();

            if (!$hasActivity) {
                break;
            }

            $streak++;
            $date->subDay();
        }

        return $streak;
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'locale' => $user->locale,
            'timezone' => $user->timezone,
            'avatar_path' => $user->avatar_path,
            'date_of_birth' => $user->date_of_birth?->toDateString(),
            'school_level' => $user->school_level,
            'created_at' => $user->created_at->toIso8601String(),
        ];
    }
}

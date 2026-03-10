<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CardProgress;
use App\Models\StudySession;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * PATCH/PUT/POST /api/v1/users/{uuid}
     * Accepts mobile's field format: first_name, last_name, avatar_url, etc.
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $user = User::findByUuidOrFail($uuid);

        // Ensure user can only update their own profile (admins bypass)
        $auth = $request->user();
        if ($auth->id !== $user->id && !in_array($auth->role->value, ['super_admin', 'admin'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'full_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'locale' => 'sometimes|string|max:5',
            'timezone' => 'sometimes|string|max:50',
            'date_of_birth' => 'sometimes|nullable|date',
            'school_level' => 'sometimes|nullable|string|max:100',
            'avatar_url' => 'sometimes|nullable|string|max:500',
            'avatar_path' => 'sometimes|nullable|string|max:500',
            'role' => 'sometimes|string|in:child,parent,learner',
        ]);

        // Build name from first_name/last_name if provided
        if (isset($validated['first_name']) || isset($validated['last_name'])) {
            $firstName = $validated['first_name'] ?? '';
            $lastName = $validated['last_name'] ?? '';
            $validated['name'] = trim("$firstName $lastName");
            unset($validated['first_name'], $validated['last_name']);
        } elseif (isset($validated['full_name'])) {
            $validated['name'] = $validated['full_name'];
            unset($validated['full_name']);
        }

        // Map avatar_url → avatar_path
        if (isset($validated['avatar_url'])) {
            $validated['avatar_path'] = $validated['avatar_url'];
            unset($validated['avatar_url']);
        }

        // Don't allow role changes unless admin
        if (isset($validated['role']) && !in_array($auth->role->value, ['super_admin', 'admin'])) {
            unset($validated['role']);
        }

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
            'platform' => 'sometimes|in:ios,android',
            'device_type' => 'sometimes|string|max:100',
        ]);

        $user = User::findByUuidOrFail($uuid);
        $platform = $request->input('platform', $request->input('device_type', 'ios'));

        // Store push token in app_settings table (key per user)
        DB::table('app_settings')->updateOrInsert(
            ['key' => "push_token:user:{$user->id}"],
            [
                'value' => json_encode([
                    'token' => $request->input('token'),
                    'platform' => $platform,
                    'device_type' => $request->input('device_type', $platform),
                    'updated_at' => now()->toIso8601String(),
                ]),
                'description' => "Push token for user {$user->id}",
                'updated_at' => now(),
            ]
        );

        return response()->json(['message' => 'Push token registered.']);
    }

    /**
     * GET /api/v1/users/{uuid}/notification-preferences
     */
    public function notificationPreferences(Request $request, string $uuid): JsonResponse
    {
        $user = User::findByUuidOrFail($uuid);

        // Get stored preferences or return defaults
        $stored = DB::table('app_settings')
            ->where('key', "notification_prefs:user:{$user->id}")
            ->first();

        $defaults = [
            'push_enabled' => true,
            'email_enabled' => true,
            'study_reminders' => true,
            'weekly_report' => true,
            'achievement_notifications' => true,
            'parent_notifications' => true,
            'message_notifications' => true,
        ];

        $prefs = $stored ? json_decode($stored->value, true) : $defaults;

        // Check if user has a push token registered
        $pushToken = DB::table('app_settings')
            ->where('key', "push_token:user:{$user->id}")
            ->first();

        $tokenData = $pushToken ? json_decode($pushToken->value, true) : null;

        return response()->json(array_merge($prefs, [
            'has_push_token' => $pushToken !== null,
            'device_type' => $tokenData['device_type'] ?? null,
            'platform' => $tokenData['platform'] ?? null,
        ]));
    }

    /**
     * PUT /api/v1/users/{uuid}/notification-preferences
     */
    public function updateNotificationPreferences(Request $request, string $uuid): JsonResponse
    {
        $user = User::findByUuidOrFail($uuid);

        // Ensure user can only update their own preferences
        $auth = $request->user();
        if ($auth->id !== $user->id && !in_array($auth->role->value, ['super_admin', 'admin'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'push_enabled' => 'sometimes|boolean',
            'email_enabled' => 'sometimes|boolean',
            'study_reminders' => 'sometimes|boolean',
            'weekly_report' => 'sometimes|boolean',
            'achievement_notifications' => 'sometimes|boolean',
            'parent_notifications' => 'sometimes|boolean',
            'message_notifications' => 'sometimes|boolean',
        ]);

        // Merge with existing preferences
        $stored = DB::table('app_settings')
            ->where('key', "notification_prefs:user:{$user->id}")
            ->first();

        $existing = $stored ? json_decode($stored->value, true) : [
            'push_enabled' => true,
            'email_enabled' => true,
            'study_reminders' => true,
            'weekly_report' => true,
            'achievement_notifications' => true,
            'parent_notifications' => true,
            'message_notifications' => true,
        ];

        $merged = array_merge($existing, $validated);

        DB::table('app_settings')->updateOrInsert(
            ['key' => "notification_prefs:user:{$user->id}"],
            [
                'value' => json_encode($merged),
                'description' => "Notification preferences for user {$user->id}",
                'updated_at' => now(),
            ]
        );

        // Return same format as GET
        $pushToken = DB::table('app_settings')
            ->where('key', "push_token:user:{$user->id}")
            ->first();

        $tokenData = $pushToken ? json_decode($pushToken->value, true) : null;

        return response()->json(array_merge($merged, [
            'has_push_token' => $pushToken !== null,
            'device_type' => $tokenData['device_type'] ?? null,
            'platform' => $tokenData['platform'] ?? null,
        ]));
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
        // Split name into first/last for mobile compatibility
        $nameParts = explode(' ', $user->name ?? '', 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        return [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'name' => $user->name,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'locale' => $user->locale,
            'timezone' => $user->timezone,
            'avatar_path' => $user->avatar_path,
            'avatar_url' => $user->avatar_path, // Alias for mobile
            'date_of_birth' => $user->date_of_birth?->toDateString(),
            'school_level' => $user->school_level,
            'is_active' => $user->is_active,
            'created_at' => $user->created_at->toIso8601String(),
            'updated_at' => $user->updated_at?->toIso8601String(),
        ];
    }
}

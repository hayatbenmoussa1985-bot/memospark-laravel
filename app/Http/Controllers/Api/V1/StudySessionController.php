<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\StudySession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudySessionController extends Controller
{
    /**
     * POST /api/v1/study-sessions
     * Start a new study session.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'deck_id' => 'required|integer|exists:decks,id',
        ]);

        $session = StudySession::create([
            'user_id' => $request->user()->id,
            'deck_id' => $request->deck_id,
            'started_at' => now(),
        ]);

        return response()->json([
            'session' => [
                'id' => $session->id,
                'deck_id' => $session->deck_id,
                'started_at' => $session->started_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * PUT /api/v1/study-sessions/{id}
     * Complete a study session.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $session = StudySession::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $request->validate([
            'cards_reviewed' => 'required|integer|min:0',
            'correct_count' => 'required|integer|min:0',
            'duration_seconds' => 'required|integer|min:0',
        ]);

        $session->update([
            'cards_reviewed' => $request->cards_reviewed,
            'correct_count' => $request->correct_count,
            'duration_seconds' => $request->duration_seconds,
            'completed_at' => now(),
        ]);

        // Log the activity
        ActivityLog::logStudy(
            userId: $request->user()->id,
            deckId: $session->deck_id,
            cardsReviewed: $request->cards_reviewed,
            successRate: $request->cards_reviewed > 0
                ? round(($request->correct_count / $request->cards_reviewed) * 100, 1)
                : 0,
            durationMinutes: (int) ceil($request->duration_seconds / 60),
        );

        return response()->json([
            'session' => [
                'id' => $session->id,
                'cards_reviewed' => $session->cards_reviewed,
                'correct_count' => $session->correct_count,
                'duration_seconds' => $session->duration_seconds,
                'accuracy_rate' => $session->accuracyRate(),
                'completed_at' => $session->completed_at->toIso8601String(),
            ],
        ]);
    }
}

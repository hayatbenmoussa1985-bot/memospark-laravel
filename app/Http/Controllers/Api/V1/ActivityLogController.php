<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * GET /activity-logs
     * List activity logs. Supports filtering by child_id, learner_id, activity_type.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ActivityLog::query();

        // Filter by child/learner
        if ($request->has('child_id')) {
            $query->where('user_id', $request->child_id);
        } elseif ($request->has('learner_id')) {
            $query->where('user_id', $request->learner_id);
        } elseif ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        } else {
            // Default: current user's own logs
            $query->where('user_id', $request->user()->id);
        }

        if ($request->has('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }

        $logs = $query->orderByDesc('created_at')
            ->limit($request->get('limit', 50))
            ->get();

        return response()->json([
            'data' => $logs->map(fn ($log) => $this->formatLog($log)),
        ]);
    }

    /**
     * POST /activity-logs
     * Create a new activity log entry.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'child_id' => 'nullable|integer',
            'activity_type' => 'required|string|max:100',
            'deck_id' => 'nullable|integer',
            'duration_minutes' => 'nullable|integer|min:0',
            'cards_reviewed' => 'nullable|integer|min:0',
            'success_rate' => 'nullable|numeric|min:0|max:100',
            'metadata' => 'nullable|array',
        ]);

        $userId = $validated['child_id'] ?? $request->user()->id;

        $log = ActivityLog::create([
            'user_id' => $userId,
            'activity_type' => $validated['activity_type'],
            'deck_id' => $validated['deck_id'] ?? null,
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'cards_reviewed' => $validated['cards_reviewed'] ?? null,
            'success_rate' => $validated['success_rate'] ?? null,
            'metadata' => $validated['metadata'] ?? null,
        ]);

        return response()->json($this->formatLog($log), 201);
    }

    /**
     * GET /activity-logs/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $log = ActivityLog::findOrFail($id);

        return response()->json($this->formatLog($log));
    }

    private function formatLog(ActivityLog $log): array
    {
        return [
            'id' => $log->id,
            'user_id' => $log->user_id,
            'activity_type' => $log->activity_type,
            'deck_id' => $log->deck_id,
            'metadata' => $log->metadata,
            'duration_minutes' => $log->duration_minutes,
            'cards_reviewed' => $log->cards_reviewed,
            'success_rate' => $log->success_rate,
            'created_at' => $log->created_at->toIso8601String(),
        ];
    }
}

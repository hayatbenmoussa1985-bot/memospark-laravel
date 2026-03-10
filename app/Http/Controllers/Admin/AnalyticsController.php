<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Card;
use App\Models\Deck;
use App\Models\ReviewLog;
use App\Models\StudySession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    /**
     * Analytics dashboard.
     */
    public function index(Request $request)
    {
        $period = $request->input('period', '7'); // days
        $since = Carbon::now()->subDays((int) $period);

        // User registration over time
        $userRegistrations = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $since)
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Study sessions over time
        $studySessions = StudySession::selectRaw('DATE(started_at) as date, COUNT(*) as count')
            ->where('started_at', '>=', $since)
            ->groupByRaw('DATE(started_at)')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Cards reviewed over time
        $cardsReviewed = StudySession::selectRaw('DATE(started_at) as date, SUM(cards_reviewed) as total')
            ->where('started_at', '>=', $since)
            ->groupByRaw('DATE(started_at)')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        // Deck creation over time
        $deckCreations = Deck::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $since)
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Average session duration
        $avgSessionDuration = StudySession::where('started_at', '>=', $since)
            ->whereNotNull('completed_at')
            ->avg('duration_seconds');

        // Average accuracy
        $avgAccuracy = StudySession::where('started_at', '>=', $since)
            ->where('cards_reviewed', '>', 0)
            ->selectRaw('AVG(correct_count * 100.0 / cards_reviewed) as avg_accuracy')
            ->value('avg_accuracy');

        // Most active users
        $topUsers = User::withCount(['studySessions' => fn ($q) => $q->where('started_at', '>=', $since)])
            ->orderByDesc('study_sessions_count')
            ->take(10)
            ->get();

        // Most popular decks (by study sessions)
        $topDecks = Deck::withCount(['studySessions' => fn ($q) => $q->where('started_at', '>=', $since)])
            ->orderByDesc('study_sessions_count')
            ->take(10)
            ->get();

        return view('admin.analytics.index', compact(
            'period',
            'userRegistrations',
            'studySessions',
            'cardsReviewed',
            'deckCreations',
            'avgSessionDuration',
            'avgAccuracy',
            'topUsers',
            'topDecks',
        ));
    }
}

<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\SM2Service;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private SM2Service $sm2,
    ) {}

    /**
     * Learner/Child dashboard.
     */
    public function index()
    {
        $user = auth()->user();

        // Redirect parent to parent dashboard
        if ($user->isParent()) {
            return redirect()->route('user.parent.dashboard');
        }

        // Due cards
        $dueCardsCount = $this->sm2->getDueCardsCount($user->id);

        // Decks with due count
        $decks = $user->decks()
            ->withCount('cards')
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($deck) use ($user) {
                $deck->due_count = $this->sm2->getDueCardsCount($user->id, $deck->id);
                return $deck;
            });

        // Study streak
        $streak = $this->calculateStreak($user);

        // Recent sessions
        $recentSessions = $user->studySessions()
            ->with('deck')
            ->latest('started_at')
            ->take(5)
            ->get();

        // Weekly stats
        $weekStart = Carbon::now()->startOfWeek();
        $sessionsThisWeek = $user->studySessions()
            ->where('started_at', '>=', $weekStart)
            ->count();
        $cardsReviewedThisWeek = $user->studySessions()
            ->where('started_at', '>=', $weekStart)
            ->sum('cards_reviewed');

        // Total stats
        $totalDecks = $user->decks()->count();
        $totalReviews = $user->cardProgress()->sum('total_reviews');

        // Badges
        $badges = $user->badges()->take(5)->get();

        return view('user.dashboard', compact(
            'dueCardsCount',
            'decks',
            'streak',
            'recentSessions',
            'sessionsThisWeek',
            'cardsReviewedThisWeek',
            'totalDecks',
            'totalReviews',
            'badges',
        ));
    }

    /**
     * Calculate consecutive days of study.
     */
    private function calculateStreak($user): int
    {
        $streak = 0;
        $date = Carbon::today();

        while (true) {
            $hasSession = $user->studySessions()
                ->whereDate('started_at', $date)
                ->exists();

            if (!$hasSession) {
                break;
            }

            $streak++;
            $date->subDay();
        }

        return $streak;
    }
}

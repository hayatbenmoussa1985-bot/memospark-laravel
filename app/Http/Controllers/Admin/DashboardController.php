<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Card;
use App\Models\Deck;
use App\Models\Report;
use App\Models\StudySession;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Core stats
        $totalUsers = User::count();
        $newUsersThisWeek = User::where('created_at', '>=', Carbon::now()->subWeek())->count();
        $totalDecks = Deck::count();
        $totalCards = Card::count();
        $activeSubscriptions = Subscription::where('status', 'active')
            ->where('current_period_end', '>', now())
            ->count();
        $pendingReports = Report::where('status', 'pending')->count();

        // Study stats
        $studySessionsToday = StudySession::whereDate('started_at', today())->count();
        $cardsReviewedToday = StudySession::whereDate('started_at', today())->sum('cards_reviewed');

        // User growth (last 7 days)
        $userGrowth = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Recent activity
        $recentActivity = ActivityLog::with('user')
            ->latest('created_at')
            ->take(10)
            ->get();

        // Role distribution
        $roleDistribution = User::selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();

        // Recent users
        $recentUsers = User::latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'newUsersThisWeek',
            'totalDecks',
            'totalCards',
            'activeSubscriptions',
            'pendingReports',
            'studySessionsToday',
            'cardsReviewedToday',
            'userGrowth',
            'recentActivity',
            'roleDistribution',
            'recentUsers',
        ));
    }
}

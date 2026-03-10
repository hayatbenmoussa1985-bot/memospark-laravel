<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Deck;
use App\Models\Message;
use App\Models\RevisionPlan;
use App\Models\User;
use App\Services\SM2Service;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    public function __construct(
        private SM2Service $sm2,
    ) {}

    /**
     * Parent dashboard — overview of all children.
     */
    public function dashboard()
    {
        $user = auth()->user();

        $children = $user->children()->get()->map(function ($child) {
            $child->total_decks = $child->decks()->count();
            $child->due_cards = $this->sm2->getDueCardsCount($child->id);
            $child->sessions_this_week = $child->studySessions()
                ->where('started_at', '>=', Carbon::now()->startOfWeek())
                ->count();
            $child->streak = $this->calculateStreak($child);
            return $child;
        });

        // Active plans
        $activePlans = RevisionPlan::where('parent_id', $user->id)
            ->active()
            ->with(['childUser', 'decks'])
            ->get();

        // Unread messages
        $unreadCount = Message::where('receiver_id', $user->id)
            ->unread()
            ->count();

        return view('user.parent.dashboard', compact('children', 'activePlans', 'unreadCount'));
    }

    /**
     * List children.
     */
    public function children()
    {
        $children = auth()->user()->children()->get()->map(function ($child) {
            $child->total_decks = $child->decks()->count();
            $child->total_sessions = $child->studySessions()->count();
            $child->total_reviews = $child->cardProgress()->sum('total_reviews');
            return $child;
        });

        return view('user.parent.children', compact('children'));
    }

    /**
     * Show child detail with stats.
     */
    public function childDetail(User $child)
    {
        $parent = auth()->user();

        // Verify this child belongs to parent
        if (!$parent->children()->where('users.id', $child->id)->exists()) {
            abort(403);
        }

        $child->load(['decks' => fn ($q) => $q->withCount('cards')->latest()->take(10)]);

        $stats = [
            'total_decks' => $child->decks()->count(),
            'total_reviews' => $child->cardProgress()->sum('total_reviews'),
            'due_cards' => $this->sm2->getDueCardsCount($child->id),
            'streak' => $this->calculateStreak($child),
            'sessions_this_week' => $child->studySessions()
                ->where('started_at', '>=', Carbon::now()->startOfWeek())
                ->count(),
        ];

        // Recent sessions
        $recentSessions = $child->studySessions()
            ->with('deck')
            ->latest('started_at')
            ->take(10)
            ->get();

        // Badges
        $badges = $child->badges()->get();

        return view('user.parent.child-detail', compact('child', 'stats', 'recentSessions', 'badges'));
    }

    /**
     * Revision plans index.
     */
    public function plans()
    {
        $plans = RevisionPlan::where('parent_id', auth()->id())
            ->with(['childUser', 'decks'])
            ->latest()
            ->get();

        return view('user.parent.plans.index', compact('plans'));
    }

    /**
     * Create plan form.
     */
    public function createPlan()
    {
        $children = auth()->user()->children()->get();
        $availableDecks = Deck::where(function ($q) {
            $q->library()->orWhere('visibility', 'public');
        })->with('category')->withCount('cards')->get();

        return view('user.parent.plans.create', compact('children', 'availableDecks'));
    }

    /**
     * Store plan.
     */
    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'child_id' => ['required', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'deck_ids' => ['required', 'array', 'min:1'],
            'deck_ids.*' => ['exists:decks,id'],
        ]);

        $parent = auth()->user();

        // Verify child belongs to parent
        if (!$parent->children()->where('users.id', $validated['child_id'])->exists()) {
            abort(403);
        }

        $plan = RevisionPlan::create([
            'parent_id' => $parent->id,
            'child_id' => $validated['child_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
        ]);

        $plan->decks()->sync($validated['deck_ids']);

        return redirect()
            ->route('user.parent.plans.index')
            ->with('success', "Plan \"{$plan->title}\" created.");
    }

    /**
     * Messages (conversations with children).
     */
    public function messages()
    {
        $user = auth()->user();
        $children = $user->children()->get();

        // Get last message per child
        $conversations = $children->map(function ($child) use ($user) {
            $child->last_message = Message::betweenUsers($user->id, $child->id)
                ->latest('created_at')
                ->first();
            $child->unread_count = Message::where('sender_id', $child->id)
                ->where('receiver_id', $user->id)
                ->unread()
                ->count();
            return $child;
        });

        return view('user.parent.messages', compact('conversations'));
    }

    /**
     * Show conversation with a child.
     */
    public function conversation(User $child)
    {
        $parent = auth()->user();

        if (!$parent->children()->where('users.id', $child->id)->exists()) {
            abort(403);
        }

        // Get messages
        $messages = Message::betweenUsers($parent->id, $child->id)
            ->orderBy('created_at')
            ->get();

        // Mark as read
        Message::where('sender_id', $child->id)
            ->where('receiver_id', $parent->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('user.parent.conversation', compact('child', 'messages'));
    }

    /**
     * Send message to child.
     */
    public function sendMessage(Request $request, User $child)
    {
        $parent = auth()->user();

        if (!$parent->children()->where('users.id', $child->id)->exists()) {
            abort(403);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        Message::create([
            'sender_id' => $parent->id,
            'receiver_id' => $child->id,
            'content' => $validated['content'],
            'created_at' => now(),
        ]);

        return back()->with('success', 'Message sent!');
    }

    private function calculateStreak($user): int
    {
        $streak = 0;
        $date = Carbon::today();

        while (true) {
            $hasSession = $user->studySessions()
                ->whereDate('started_at', $date)
                ->exists();

            if (!$hasSession) break;

            $streak++;
            $date->subDay();
        }

        return $streak;
    }
}

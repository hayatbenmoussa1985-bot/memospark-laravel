<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Deck;
use App\Models\StudySession;
use App\Services\SM2Service;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StudyController extends Controller
{
    public function __construct(
        private SM2Service $sm2,
    ) {}

    /**
     * Show all due cards across all decks.
     */
    public function due()
    {
        $user = auth()->user();

        $decks = $user->decks()
            ->withCount('cards')
            ->get()
            ->map(function ($deck) use ($user) {
                $deck->due_count = $this->sm2->getDueCardsCount($user->id, $deck->id);
                return $deck;
            })
            ->filter(fn ($deck) => $deck->due_count > 0)
            ->sortByDesc('due_count');

        $totalDue = $decks->sum('due_count');

        return view('user.study.due', compact('decks', 'totalDue'));
    }

    /**
     * Start a study session for a deck.
     */
    public function start(Deck $deck)
    {
        $user = auth()->user();

        // Get due cards
        $dueCards = $this->sm2->getDueCards($user->id, $deck->id, 20);

        if ($dueCards->isEmpty()) {
            return redirect()
                ->route('user.decks.show', $deck)
                ->with('success', 'No cards due for review right now!');
        }

        // Create study session
        $session = StudySession::create([
            'user_id' => $user->id,
            'deck_id' => $deck->id,
            'started_at' => now(),
        ]);

        // Get first card
        $currentCard = $dueCards->first();
        $currentCard->load('mcqOptions');

        return view('user.study.session', [
            'session' => $session,
            'deck' => $deck,
            'card' => $currentCard,
            'cardIndex' => 1,
            'totalCards' => $dueCards->count(),
            'dueCardIds' => $dueCards->pluck('id')->toArray(),
        ]);
    }

    /**
     * Submit a card review (AJAX or form).
     */
    public function review(Request $request)
    {
        $validated = $request->validate([
            'session_id' => ['required', 'exists:study_sessions,id'],
            'card_id' => ['required', 'exists:cards,id'],
            'quality' => ['required', 'integer', 'min:0', 'max:5'],
            'time_spent_ms' => ['nullable', 'integer', 'min:0'],
        ]);

        $user = auth()->user();

        // Perform SM-2 review
        $progress = $this->sm2->review(
            userId: $user->id,
            cardId: $validated['card_id'],
            quality: $validated['quality'],
            timeSpentMs: $validated['time_spent_ms'] ?? null,
            sessionId: $validated['session_id'],
        );

        // Update session counters
        $session = StudySession::findOrFail($validated['session_id']);
        $session->increment('cards_reviewed');
        if ($validated['quality'] >= 3) {
            $session->increment('correct_count');
        }

        return response()->json([
            'success' => true,
            'next_review' => $progress->next_review_at?->toISOString(),
            'easiness_factor' => $progress->easiness_factor,
            'interval_days' => $progress->interval_days,
        ]);
    }

    /**
     * Get next due card for a session (AJAX).
     */
    public function nextCard(Request $request)
    {
        $validated = $request->validate([
            'session_id' => ['required', 'exists:study_sessions,id'],
            'deck_id' => ['required', 'exists:decks,id'],
            'reviewed_ids' => ['nullable', 'array'],
            'reviewed_ids.*' => ['integer'],
        ]);

        $user = auth()->user();
        $reviewedIds = $validated['reviewed_ids'] ?? [];

        $nextCard = $this->sm2->getDueCards($user->id, $validated['deck_id'], 1)
            ->reject(fn ($card) => in_array($card->id, $reviewedIds))
            ->first();

        if (!$nextCard) {
            return response()->json(['finished' => true]);
        }

        $nextCard->load('mcqOptions');

        return response()->json([
            'finished' => false,
            'card' => [
                'id' => $nextCard->id,
                'front_text' => $nextCard->front_text,
                'back_text' => $nextCard->back_text,
                'hint' => $nextCard->hint,
                'front_image_path' => $nextCard->front_image_path,
                'back_image_path' => $nextCard->back_image_path,
                'mcq_options' => $nextCard->mcqOptions->map(fn ($o) => [
                    'id' => $o->id,
                    'option_text' => $o->option_text,
                ]),
            ],
        ]);
    }

    /**
     * Complete a study session.
     */
    public function complete(Request $request)
    {
        $validated = $request->validate([
            'session_id' => ['required', 'exists:study_sessions,id'],
        ]);

        $session = StudySession::findOrFail($validated['session_id']);
        $user = auth()->user();

        // Calculate duration
        $session->update([
            'completed_at' => now(),
            'duration_seconds' => now()->diffInSeconds($session->started_at),
        ]);

        // Log activity
        ActivityLog::logStudy(
            userId: $user->id,
            deckId: $session->deck_id,
            metadata: [
                'session_id' => $session->id,
                'cards_reviewed' => $session->cards_reviewed,
                'correct_count' => $session->correct_count,
                'duration_seconds' => $session->duration_seconds,
            ],
        );

        $session->load('deck');

        return view('user.study.complete', [
            'session' => $session,
            'accuracy' => $session->accuracyRate(),
        ]);
    }
}

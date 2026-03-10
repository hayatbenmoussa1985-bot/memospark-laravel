<?php

namespace App\Services;

use App\Models\Card;
use App\Models\CardProgress;
use App\Models\ReviewLog;
use App\Models\StudySession;

/**
 * SM-2 Spaced Repetition Algorithm Service.
 *
 * Implements the SuperMemo SM-2 algorithm:
 * - quality 0-2: reset (incorrect answer)
 * - quality 3-5: advance (correct answer)
 * - Easiness Factor: min 1.30, calculated per review
 * - Interval: 1 day, 6 days, then EF * previous interval
 */
class SM2Service
{
    /**
     * Process a card review using SM-2 algorithm.
     *
     * @param int $userId The reviewing user's ID
     * @param int $cardId The card being reviewed
     * @param int $quality Quality of response (0-5):
     *   0 = complete blackout
     *   1 = wrong, but recognized answer when shown
     *   2 = wrong, but answer was easy to recall
     *   3 = correct, but with serious difficulty
     *   4 = correct, with some hesitation
     *   5 = perfect response
     * @param int|null $timeSpentMs Time spent on the card in milliseconds
     * @param int|null $sessionId Study session ID (if applicable)
     * @return CardProgress Updated progress record
     */
    public function review(int $userId, int $cardId, int $quality, ?int $timeSpentMs = null, ?int $sessionId = null): CardProgress
    {
        $quality = max(0, min(5, $quality));

        // Get or create progress record
        $progress = CardProgress::firstOrCreate(
            ['user_id' => $userId, 'card_id' => $cardId],
            [
                'easiness_factor' => 2.50,
                'interval_days' => 0,
                'repetitions' => 0,
                'next_review_at' => now(),
                'total_reviews' => 0,
                'correct_reviews' => 0,
            ]
        );

        // Store previous values for the review log
        $efBefore = $progress->easiness_factor;
        $intervalBefore = $progress->interval_days;

        // Calculate new SM-2 values
        $result = $this->calculate($progress, $quality);

        // Update progress
        $progress->update([
            'easiness_factor' => $result['easiness_factor'],
            'interval_days' => $result['interval_days'],
            'repetitions' => $result['repetitions'],
            'next_review_at' => now()->addDays($result['interval_days']),
            'last_reviewed_at' => now(),
            'total_reviews' => $progress->total_reviews + 1,
            'correct_reviews' => $quality >= 3
                ? $progress->correct_reviews + 1
                : $progress->correct_reviews,
        ]);

        // Create review log entry
        ReviewLog::create([
            'user_id' => $userId,
            'card_id' => $cardId,
            'session_id' => $sessionId,
            'quality' => $quality,
            'easiness_factor_before' => $efBefore,
            'easiness_factor_after' => $result['easiness_factor'],
            'interval_before' => $intervalBefore,
            'interval_after' => $result['interval_days'],
            'time_spent_ms' => $timeSpentMs,
            'reviewed_at' => now(),
        ]);

        return $progress->fresh();
    }

    /**
     * Core SM-2 calculation.
     *
     * @return array{easiness_factor: float, interval_days: int, repetitions: int}
     */
    public function calculate(CardProgress $progress, int $quality): array
    {
        $ef = (float) $progress->easiness_factor;
        $interval = $progress->interval_days;
        $reps = $progress->repetitions;

        // Calculate new Easiness Factor
        // EF' = EF + (0.1 - (5 - q) * (0.08 + (5 - q) * 0.02))
        $newEf = $ef + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
        $newEf = max(1.30, round($newEf, 2)); // Minimum EF is 1.30

        if ($quality >= 3) {
            // Correct response — advance
            $reps++;

            if ($reps === 1) {
                $interval = 1;
            } elseif ($reps === 2) {
                $interval = 6;
            } else {
                $interval = (int) ceil($interval * $newEf);
            }
        } else {
            // Incorrect response — reset
            $reps = 0;
            $interval = 1;
        }

        return [
            'easiness_factor' => $newEf,
            'interval_days' => $interval,
            'repetitions' => $reps,
        ];
    }

    /**
     * Get cards due for review for a user in a specific deck.
     *
     * @return \Illuminate\Database\Eloquent\Collection<Card>
     */
    public function getDueCards(int $userId, int $deckId, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        $deck = \App\Models\Deck::findOrFail($deckId);

        // Cards with progress that are due
        $dueCardIds = CardProgress::where('user_id', $userId)
            ->whereIn('card_id', $deck->cards()->pluck('id'))
            ->where('next_review_at', '<=', now())
            ->pluck('card_id');

        // Cards never studied (no progress record)
        $studiedCardIds = CardProgress::where('user_id', $userId)
            ->whereIn('card_id', $deck->cards()->pluck('id'))
            ->pluck('card_id');

        $newCardIds = $deck->cards()
            ->whereNotIn('id', $studiedCardIds)
            ->pluck('id');

        // Combine: due cards first, then new cards
        $allDueIds = $dueCardIds->merge($newCardIds)->take($limit);

        return Card::whereIn('id', $allDueIds)
            ->orderByRaw("FIELD(id, " . $allDueIds->implode(',') . ")")
            ->get();
    }

    /**
     * Get count of due cards for a user across all their decks.
     */
    public function getDueCardsCount(int $userId, ?int $deckId = null): int
    {
        $query = CardProgress::where('user_id', $userId)
            ->where('next_review_at', '<=', now());

        if ($deckId) {
            $deckCardIds = Card::where('deck_id', $deckId)->pluck('id');
            $query->whereIn('card_id', $deckCardIds);
        }

        $dueCount = $query->count();

        // Also count cards never studied
        $studiedCardIds = CardProgress::where('user_id', $userId)->pluck('card_id');

        $newQuery = Card::whereNotIn('id', $studiedCardIds);
        if ($deckId) {
            $newQuery->where('deck_id', $deckId);
        }
        $newCount = $newQuery->count();

        return $dueCount + $newCount;
    }
}

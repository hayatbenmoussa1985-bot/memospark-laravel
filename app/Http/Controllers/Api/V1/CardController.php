<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Deck;
use App\Services\SM2Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function __construct(
        private SM2Service $sm2Service,
    ) {}

    /**
     * GET /api/v1/decks/{uuid}/cards
     */
    public function index(string $deckUuid): JsonResponse
    {
        $deck = Deck::findByUuidOrFail($deckUuid);

        $cards = $deck->cards()
            ->with('mcqOptions')
            ->orderBy('position')
            ->get();

        return response()->json([
            'cards' => $cards->map(fn ($c) => $this->formatCard($c)),
        ]);
    }

    /**
     * POST /api/v1/decks/{uuid}/cards
     */
    public function store(Request $request, string $deckUuid): JsonResponse
    {
        $deck = Deck::findByUuidOrFail($deckUuid);

        if ($request->user()->id !== $deck->user_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'front_text' => 'required|string',
            'back_text' => 'required|string',
            'front_image_url' => 'nullable|string|max:500',
            'back_image_url' => 'nullable|string|max:500',
            'front_audio_url' => 'nullable|string|max:500',
            'back_audio_url' => 'nullable|string|max:500',
            'hint' => 'nullable|string',
            'explanation' => 'nullable|string',
            'position' => 'nullable|integer',
            'is_mcq' => 'sometimes|boolean',
            'mcq_question' => 'nullable|string',
            'mcq_options' => 'nullable|array',
            'mcq_options.*.option_text' => 'required_with:mcq_options|string',
            'mcq_options.*.is_correct' => 'required_with:mcq_options|boolean',
        ]);

        $card = $deck->cards()->create([
            ...\Illuminate\Support\Arr::except($validated, ['mcq_options']),
            'position' => $validated['position'] ?? $deck->cards()->count(),
        ]);

        // Create MCQ options if provided
        if (!empty($validated['mcq_options'])) {
            foreach ($validated['mcq_options'] as $i => $option) {
                $card->mcqOptions()->create([
                    ...$option,
                    'position' => $i,
                ]);
            }
        }

        // Update deck cards_count
        $deck->updateCardsCount();

        return response()->json([
            'card' => $this->formatCard($card->load('mcqOptions')),
        ], 201);
    }

    /**
     * GET /api/v1/cards/{uuid}
     */
    public function show(string $uuid): JsonResponse
    {
        $card = Card::where('uuid', $uuid)->with('mcqOptions')->firstOrFail();

        return response()->json(['card' => $this->formatCard($card)]);
    }

    /**
     * PUT /api/v1/cards/{uuid}
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $card = Card::findByUuidOrFail($uuid);

        if ($request->user()->id !== $card->deck->user_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'front_text' => 'sometimes|string',
            'back_text' => 'sometimes|string',
            'front_image_url' => 'sometimes|nullable|string|max:500',
            'back_image_url' => 'sometimes|nullable|string|max:500',
            'front_audio_url' => 'sometimes|nullable|string|max:500',
            'back_audio_url' => 'sometimes|nullable|string|max:500',
            'hint' => 'sometimes|nullable|string',
            'explanation' => 'sometimes|nullable|string',
            'position' => 'sometimes|integer',
            'is_mcq' => 'sometimes|boolean',
            'mcq_question' => 'sometimes|nullable|string',
        ]);

        $card->update($validated);

        return response()->json(['card' => $this->formatCard($card->fresh()->load('mcqOptions'))]);
    }

    /**
     * DELETE /api/v1/cards/{uuid}
     */
    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $card = Card::findByUuidOrFail($uuid);

        if ($request->user()->id !== $card->deck->user_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $deckId = $card->deck_id;
        $card->delete(); // Soft delete

        Deck::find($deckId)?->updateCardsCount();

        return response()->json(['message' => 'Card deleted.']);
    }

    /**
     * POST /api/v1/cards/{uuid}/review
     * Submit an SM-2 review for a card.
     */
    public function review(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'quality' => 'required|integer|min:0|max:5',
            'time_spent_ms' => 'nullable|integer|min:0',
            'session_id' => 'nullable|integer|exists:study_sessions,id',
        ]);

        $card = Card::findByUuidOrFail($uuid);

        $progress = $this->sm2Service->review(
            userId: $request->user()->id,
            cardId: $card->id,
            quality: $request->quality,
            timeSpentMs: $request->time_spent_ms,
            sessionId: $request->session_id,
        );

        return response()->json([
            'progress' => [
                'easiness_factor' => $progress->easiness_factor,
                'interval_days' => $progress->interval_days,
                'repetitions' => $progress->repetitions,
                'next_review_at' => $progress->next_review_at->toIso8601String(),
                'total_reviews' => $progress->total_reviews,
                'correct_reviews' => $progress->correct_reviews,
                'accuracy_rate' => $progress->accuracyRate(),
            ],
        ]);
    }

    private function formatCard(Card $card): array
    {
        return [
            'id' => $card->id,
            'uuid' => $card->uuid,
            'deck_id' => $card->deck_id,
            'front_text' => $card->front_text,
            'back_text' => $card->back_text,
            'front_image_url' => $card->front_image_url,
            'back_image_url' => $card->back_image_url,
            'front_audio_url' => $card->front_audio_url,
            'back_audio_url' => $card->back_audio_url,
            'hint' => $card->hint,
            'explanation' => $card->explanation,
            'position' => $card->position,
            'is_mcq' => $card->is_mcq,
            'mcq_question' => $card->mcq_question,
            'mcq_options' => $card->mcqOptions?->map(fn ($o) => [
                'id' => $o->id,
                'option_text' => $o->option_text,
                'option_image_url' => $o->option_image_url,
                'is_correct' => $o->is_correct,
                'position' => $o->position,
            ]),
            'created_at' => $card->created_at->toIso8601String(),
        ];
    }
}

<x-user-layout :title="'Study: ' . $deck->title">

    <div class="max-w-2xl mx-auto" x-data="studySession()" x-init="init()">

        {{-- Progress bar --}}
        <div class="mb-6">
            <div class="flex items-center justify-between text-sm text-gray-500 mb-2">
                <span>{{ $deck->title }}</span>
                <span x-text="reviewedCount + ' / ' + totalCards">{{ $cardIndex }} / {{ $totalCards }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-emerald-500 h-2 rounded-full transition-all duration-300"
                     :style="'width: ' + (reviewedCount / totalCards * 100) + '%'"></div>
            </div>
        </div>

        {{-- Flashcard --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">

            {{-- Card content --}}
            <div class="p-8 min-h-[300px] flex flex-col items-center justify-center text-center">

                {{-- Front --}}
                <div x-show="!showAnswer">
                    <p class="text-xs font-medium text-gray-400 uppercase mb-3">Question</p>
                    <p class="text-xl text-gray-900 leading-relaxed" x-text="currentCard.front_text">{{ $card->front_text }}</p>
                    @if($card->hint)
                        <div x-show="showHint" x-cloak class="mt-4">
                            <p class="text-sm text-amber-600" x-text="currentCard.hint">{{ $card->hint }}</p>
                        </div>
                    @endif
                </div>

                {{-- Back --}}
                <div x-show="showAnswer" x-cloak>
                    <p class="text-xs font-medium text-gray-400 uppercase mb-3">Answer</p>
                    <p class="text-xl text-emerald-700 leading-relaxed" x-text="currentCard.back_text">{{ $card->back_text }}</p>
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <p class="text-xs text-gray-400">Question was:</p>
                        <p class="text-sm text-gray-500" x-text="currentCard.front_text">{{ $card->front_text }}</p>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="border-t border-gray-200 p-4">
                {{-- Show Answer button --}}
                <div x-show="!showAnswer" class="text-center space-y-2">
                    <template x-if="currentCard.hint">
                        <button @click="showHint = true" x-show="!showHint" class="text-sm text-amber-600 hover:underline block mx-auto mb-2">
                            Show Hint
                        </button>
                    </template>
                    <button @click="showAnswer = true; stopTimer()" class="w-full py-3 bg-gray-900 text-white text-sm font-medium rounded-xl hover:bg-gray-800">
                        Show Answer
                    </button>
                </div>

                {{-- Quality rating --}}
                <div x-show="showAnswer" x-cloak>
                    <p class="text-sm text-gray-500 text-center mb-3">How well did you know this?</p>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                        <button @click="submitReview(0)" class="py-2 px-2 text-xs font-medium rounded-lg bg-red-50 text-red-700 hover:bg-red-100 border border-red-200">
                            0 · Blank
                        </button>
                        <button @click="submitReview(1)" class="py-2 px-2 text-xs font-medium rounded-lg bg-red-50 text-red-600 hover:bg-red-100 border border-red-200">
                            1 · Wrong
                        </button>
                        <button @click="submitReview(2)" class="py-2 px-2 text-xs font-medium rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200">
                            2 · Hard
                        </button>
                        <button @click="submitReview(3)" class="py-2 px-2 text-xs font-medium rounded-lg bg-yellow-50 text-yellow-700 hover:bg-yellow-100 border border-yellow-200">
                            3 · Ok
                        </button>
                        <button @click="submitReview(4)" class="py-2 px-2 text-xs font-medium rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200">
                            4 · Good
                        </button>
                        <button @click="submitReview(5)" class="py-2 px-2 text-xs font-medium rounded-lg bg-emerald-100 text-emerald-800 hover:bg-emerald-200 border border-emerald-300">
                            5 · Easy
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Loading --}}
        <div x-show="loading" x-cloak class="text-center text-sm text-gray-500">Loading next card...</div>

    </div>

    <script>
    function studySession() {
        return {
            sessionId: {{ $session->id }},
            deckId: {{ $deck->id }},
            currentCard: {
                id: {{ $card->id }},
                front_text: @js($card->front_text),
                back_text: @js($card->back_text),
                hint: @js($card->hint),
            },
            reviewedCount: 0,
            totalCards: {{ $totalCards }},
            reviewedIds: [],
            showAnswer: false,
            showHint: false,
            loading: false,
            timerStart: null,

            init() {
                this.startTimer();
            },

            startTimer() {
                this.timerStart = Date.now();
            },

            stopTimer() {
                // Timer still accessible via timerStart
            },

            async submitReview(quality) {
                this.loading = true;
                const timeSpent = Date.now() - this.timerStart;

                try {
                    // Submit review
                    await fetch('{{ route("user.study.review") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            session_id: this.sessionId,
                            card_id: this.currentCard.id,
                            quality: quality,
                            time_spent_ms: timeSpent,
                        }),
                    });

                    this.reviewedIds.push(this.currentCard.id);
                    this.reviewedCount++;

                    // Get next card
                    const resp = await fetch('{{ route("user.study.next-card") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            session_id: this.sessionId,
                            deck_id: this.deckId,
                            reviewed_ids: this.reviewedIds,
                        }),
                    });
                    const data = await resp.json();

                    if (data.finished) {
                        // Complete session
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("user.study.complete") }}';
                        form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="session_id" value="${this.sessionId}">`;
                        document.body.appendChild(form);
                        form.submit();
                        return;
                    }

                    this.currentCard = data.card;
                    this.showAnswer = false;
                    this.showHint = false;
                    this.startTimer();
                } catch (e) {
                    console.error('Review error:', e);
                } finally {
                    this.loading = false;
                }
            }
        };
    }
    </script>

</x-user-layout>

<x-user-layout title="Session Complete">

    <div class="max-w-md mx-auto text-center py-8">

        {{-- Success icon --}}
        <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-2">Session Complete!</h1>
        <p class="text-gray-500 mb-8">Great job studying <span class="font-medium text-gray-700">{{ $session->deck?->title }}</span></p>

        {{-- Stats --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $session->cards_reviewed }}</p>
                    <p class="text-xs text-gray-500">Cards Reviewed</p>
                </div>
                <div>
                    <p class="text-2xl font-bold {{ $accuracy >= 70 ? 'text-emerald-600' : ($accuracy >= 50 ? 'text-amber-600' : 'text-red-600') }}">
                        {{ round($accuracy) }}%
                    </p>
                    <p class="text-xs text-gray-500">Accuracy</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $session->formattedDuration() }}</p>
                    <p class="text-xs text-gray-500">Duration</p>
                </div>
            </div>
        </div>

        {{-- Accuracy feedback --}}
        @if($accuracy >= 80)
            <p class="text-emerald-600 font-medium mb-6">Excellent work! You're mastering these cards! 🎉</p>
        @elseif($accuracy >= 60)
            <p class="text-amber-600 font-medium mb-6">Good effort! Keep reviewing to improve. 💪</p>
        @else
            <p class="text-red-600 font-medium mb-6">Keep practicing! These cards need more review. 📚</p>
        @endif

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('user.study.start', $session->deck) }}" class="px-6 py-3 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700">
                Study Again
            </a>
            <a href="{{ route('user.dashboard') }}" class="px-6 py-3 text-sm text-gray-600 border border-gray-300 rounded-xl hover:bg-gray-50">
                Back to Dashboard
            </a>
        </div>

    </div>

</x-user-layout>

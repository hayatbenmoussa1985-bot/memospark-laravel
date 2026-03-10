<x-user-layout title="Due Cards">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Cards Due for Review</h1>
        <p class="text-sm text-gray-500">{{ $totalDue }} cards across {{ $decks->count() }} decks</p>
    </div>

    <div class="space-y-4">
        @forelse($decks as $deck)
            <div class="bg-white rounded-xl border border-gray-200 p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">{{ $deck->title }}</h3>
                    <p class="text-sm text-gray-500">{{ $deck->due_count }} cards due · {{ $deck->cards_count }} total</p>
                </div>
                <a href="{{ route('user.study.start', $deck) }}" class="px-6 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 text-center">
                    Study {{ $deck->due_count }} Cards
                </a>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                <div class="mb-4">
                    <svg class="w-12 h-12 text-emerald-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">All caught up!</h3>
                <p class="text-gray-500">No cards are due for review right now. Check back later!</p>
            </div>
        @endforelse
    </div>

</x-user-layout>

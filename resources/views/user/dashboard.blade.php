<x-user-layout title="Dashboard">

    {{-- Greeting + Streak --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ auth()->user()->name }}!</h1>
            <p class="text-sm text-gray-500">Keep up the good work!</p>
        </div>
        <x-user.streak-badge :streak="$streak" />
    </div>

    {{-- Due cards CTA --}}
    @if($dueCardsCount > 0)
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-5 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-emerald-900">You have {{ $dueCardsCount }} cards to review!</h2>
                    <p class="text-sm text-emerald-700">Keep your streak going by studying today.</p>
                </div>
                <a href="{{ route('user.study.due') }}" class="px-6 py-3 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700 text-center whitespace-nowrap">
                    Start Studying
                </a>
            </div>
        </div>
    @endif

    {{-- Quick Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $totalDecks }}</p>
            <p class="text-xs text-gray-500">My Decks</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600">{{ $dueCardsCount }}</p>
            <p class="text-xs text-gray-500">Cards Due</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $sessionsThisWeek }}</p>
            <p class="text-xs text-gray-500">Sessions This Week</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $cardsReviewedThisWeek }}</p>
            <p class="text-xs text-gray-500">Cards This Week</p>
        </div>
    </div>

    {{-- My Decks --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">My Decks</h2>
            <div class="flex gap-2">
                <a href="{{ route('user.decks.create') }}" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">+ New Deck</a>
                <a href="{{ route('user.decks.index') }}" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">View All</a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($decks as $deck)
                <x-user.deck-card :deck="$deck" :dueCount="$deck->due_count">
                    @if($deck->due_count > 0)
                        <a href="{{ route('user.study.start', $deck) }}" class="flex-1 text-center px-3 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">Study Now</a>
                    @endif
                    <a href="{{ route('user.decks.show', $deck) }}" class="px-3 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">View</a>
                </x-user.deck-card>
            @empty
                <div class="col-span-full bg-white rounded-xl border border-gray-200 p-8 text-center">
                    <p class="text-gray-500 mb-2">No decks yet.</p>
                    <a href="{{ route('user.decks.create') }}" class="text-sm text-emerald-600 hover:underline">Create your first deck</a>
                    or
                    <a href="{{ route('user.library.index') }}" class="text-sm text-emerald-600 hover:underline">browse the library</a>
                </div>
            @endforelse
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Sessions --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">Recent Study Sessions</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentSessions as $session)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $session->deck?->title ?? 'Unknown' }}</p>
                            <p class="text-xs text-gray-500">{{ $session->cards_reviewed }} cards · {{ $session->accuracyRate() }}% accuracy</p>
                        </div>
                        <span class="text-xs text-gray-400">{{ $session->started_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-gray-500">No sessions yet. Start studying!</div>
                @endforelse
            </div>
        </div>

        {{-- Badges --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">My Badges</h3>
            </div>
            <div class="p-6">
                @if($badges->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        @foreach($badges as $badge)
                            <div class="flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 border border-amber-200 rounded-full">
                                <span>{{ $badge->icon }}</span>
                                <span class="text-sm font-medium text-amber-800">{{ $badge->name }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center">Complete study sessions to earn badges!</p>
                @endif
            </div>
        </div>
    </div>

</x-user-layout>

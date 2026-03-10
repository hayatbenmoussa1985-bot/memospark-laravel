<x-user-layout :title="$child->name">

    <div class="mb-6">
        <a href="{{ route('user.parent.children') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Children
        </a>
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center text-xl font-bold text-emerald-700">
                {{ strtoupper(substr($child->name, 0, 1)) }}
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $child->name }}</h1>
                <p class="text-sm text-gray-500">{{ $child->school_level ?? 'Student' }} · {{ $child->email }}</p>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_decks'] }}</p>
            <p class="text-xs text-gray-500">Decks</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_reviews'] }}</p>
            <p class="text-xs text-gray-500">Reviews</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600">{{ $stats['due_cards'] }}</p>
            <p class="text-xs text-gray-500">Cards Due</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $stats['sessions_this_week'] }}</p>
            <p class="text-xs text-gray-500">This Week</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">🔥 {{ $stats['streak'] }}</p>
            <p class="text-xs text-gray-500">Streak</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Decks --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">Decks ({{ $child->decks->count() }})</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($child->decks as $deck)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $deck->title }}</p>
                            <p class="text-xs text-gray-500">{{ $deck->cards_count }} cards</p>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-gray-500">No decks yet.</div>
                @endforelse
            </div>
        </div>

        {{-- Recent Sessions --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">Recent Sessions</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentSessions as $session)
                    <div class="px-6 py-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $session->deck?->title ?? 'Unknown Deck' }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $session->cards_reviewed }} cards · {{ $session->accuracyRate() }}% accuracy
                                </p>
                            </div>
                            <span class="text-xs text-gray-400">{{ $session->started_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-gray-500">No study sessions yet.</div>
                @endforelse
            </div>
        </div>

        {{-- Badges --}}
        @if($badges->isNotEmpty())
            <div class="bg-white rounded-xl border border-gray-200 lg:col-span-2">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-900">Badges Earned</h3>
                </div>
                <div class="p-6 flex flex-wrap gap-2">
                    @foreach($badges as $badge)
                        <div class="flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 border border-amber-200 rounded-full">
                            <span>{{ $badge->icon }}</span>
                            <span class="text-sm font-medium text-amber-800">{{ $badge->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

</x-user-layout>

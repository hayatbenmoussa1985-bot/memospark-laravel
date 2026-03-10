<x-user-layout :title="$deck->title">

    <div class="mb-4">
        <a href="{{ route('user.decks.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            My Decks
        </a>
    </div>

    {{-- Deck Header --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $deck->title }}</h1>
                @if($deck->description)
                    <p class="text-sm text-gray-600 mt-1">{{ $deck->description }}</p>
                @endif
                <div class="flex flex-wrap items-center gap-3 mt-3 text-sm text-gray-500">
                    <span>{{ $totalCards }} cards</span>
                    @if($deck->category)
                        <span>{{ $deck->category->icon }} {{ $deck->category->slug }}</span>
                    @endif
                    @if($dueCount > 0)
                        <span class="text-emerald-600 font-medium">{{ $dueCount }} due</span>
                    @endif
                </div>
            </div>
            <div class="flex gap-2 shrink-0">
                @if($dueCount > 0)
                    <a href="{{ route('user.study.start', $deck) }}" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">Study Now</a>
                @endif
                <a href="{{ route('user.decks.edit', $deck) }}" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Edit</a>
            </div>
        </div>
    </div>

    {{-- Add Card Form --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6" x-data="{ open: false }">
        <button @click="open = !open" class="flex items-center gap-2 text-sm font-medium text-emerald-600 hover:text-emerald-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Card
        </button>
        <form method="POST" action="{{ route('user.decks.cards.store', $deck) }}" x-show="open" x-transition class="mt-4" x-cloak>
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Front (Question)</label>
                    <textarea name="front_text" rows="3" required class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Back (Answer)</label>
                    <textarea name="back_text" rows="3" required class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Hint (optional)</label>
                <input type="text" name="hint" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">Add Card</button>
        </form>
    </div>

    {{-- Cards List --}}
    <div class="space-y-3">
        @forelse($cards as $card)
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-start gap-4">
                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase mb-1">Front</p>
                            <p class="text-sm text-gray-900">{{ Str::limit($card->front_text, 150) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase mb-1">Back</p>
                            <p class="text-sm text-gray-700">{{ Str::limit($card->back_text, 150) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if($card->progress)
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $card->progress->isDue() ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $card->progress->isDue() ? 'Due' : 'Reviewed' }}
                            </span>
                        @else
                            <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">New</span>
                        @endif
                        <form method="POST" action="{{ route('user.decks.cards.destroy', [$deck, $card]) }}" onsubmit="return confirm('Delete this card?')">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                <p class="text-gray-500">No cards yet. Add your first card above!</p>
            </div>
        @endforelse
    </div>

</x-user-layout>

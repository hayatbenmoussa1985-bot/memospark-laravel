<x-admin-layout :title="$deck->title">

    <div class="mb-6">
        <a href="{{ route('admin.decks.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Decks
        </a>
    </div>

    {{-- Deck Header --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $deck->title }}</h2>
                @if($deck->description)
                    <p class="mt-1 text-sm text-gray-600">{{ $deck->description }}</p>
                @endif
                <div class="mt-3 flex flex-wrap items-center gap-2 text-sm text-gray-500">
                    <span>By {{ $deck->user?->name ?? 'Unknown' }}</span>
                    <span>&middot;</span>
                    <span>{{ $deck->cards->count() }} cards</span>
                    <span>&middot;</span>
                    <x-admin.badge :color="match($deck->visibility->value ?? $deck->visibility) {
                        'private' => 'gray', 'public' => 'blue', 'library' => 'emerald', default => 'gray',
                    }">{{ ucfirst($deck->visibility->value ?? $deck->visibility) }}</x-admin.badge>
                    @if($deck->is_featured)
                        <x-admin.badge color="amber">★ Featured</x-admin.badge>
                    @endif
                    @if($deck->category)
                        <x-admin.badge color="indigo">{{ $deck->category->icon }} {{ $deck->category->slug }}</x-admin.badge>
                    @endif
                </div>
            </div>
            <div class="flex gap-2 shrink-0">
                <a href="{{ route('admin.decks.edit', $deck) }}" class="px-4 py-2 text-sm font-medium bg-gray-900 text-white rounded-lg hover:bg-gray-800">Edit</a>
                <form method="POST" action="{{ route('admin.decks.destroy', $deck) }}" onsubmit="return confirm('Delete this deck?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100">Delete</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Cards --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-900">Cards ({{ $deck->cards->count() }})</h3>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($deck->cards as $card)
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase mb-1">Front</p>
                            <p class="text-sm text-gray-900">{{ Str::limit($card->front_text, 200) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase mb-1">Back</p>
                            <p class="text-sm text-gray-700">{{ Str::limit($card->back_text, 200) }}</p>
                        </div>
                    </div>
                    @if($card->hint)
                        <p class="mt-2 text-xs text-gray-500">Hint: {{ $card->hint }}</p>
                    @endif
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-gray-500">No cards in this deck.</div>
            @endforelse
        </div>
    </div>

</x-admin-layout>

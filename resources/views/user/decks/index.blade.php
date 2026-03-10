<x-user-layout title="My Decks">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Decks</h1>
            <p class="text-sm text-gray-500">{{ $decks->count() }} decks</p>
        </div>
        <a href="{{ route('user.decks.create') }}" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
            + New Deck
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($decks as $deck)
            <x-user.deck-card :deck="$deck" :dueCount="$deck->due_count">
                @if($deck->due_count > 0)
                    <a href="{{ route('user.study.start', $deck) }}" class="flex-1 text-center px-3 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">Study</a>
                @endif
                <a href="{{ route('user.decks.show', $deck) }}" class="px-3 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">View</a>
                <a href="{{ route('user.decks.edit', $deck) }}" class="px-3 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Edit</a>
            </x-user.deck-card>
        @empty
            <div class="col-span-full bg-white rounded-xl border border-gray-200 p-8 text-center">
                <div class="mb-4">
                    <svg class="w-12 h-12 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <p class="text-gray-500 mb-2">No decks yet.</p>
                <a href="{{ route('user.decks.create') }}" class="inline-block px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">Create Your First Deck</a>
            </div>
        @endforelse
    </div>

</x-user-layout>

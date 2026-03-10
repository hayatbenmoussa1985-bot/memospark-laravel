<x-user-layout title="My Favorites">

    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">My Favorites</h1>
                <p class="text-sm text-gray-500">Decks you've saved from the library</p>
            </div>
            <a href="{{ route('user.library.index') }}" class="text-sm text-emerald-600 hover:underline">
                Browse Library &rarr;
            </a>
        </div>
    </div>

    @if($decks->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($decks as $deck)
                <div class="bg-white rounded-xl border border-gray-200 p-5 hover:border-emerald-300 hover:shadow-sm transition-all">
                    <div class="flex items-start justify-between mb-3">
                        <a href="{{ route('user.library.show', $deck) }}" class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900 hover:text-emerald-700 transition-colors line-clamp-2">
                                {{ $deck->title }}
                            </h3>
                        </a>
                        <form method="POST" action="{{ route('user.library.unfavorite', $deck) }}" class="shrink-0 ml-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-amber-500 hover:text-amber-600" title="Remove from favorites">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </button>
                        </form>
                    </div>

                    @if($deck->description)
                        <p class="text-sm text-gray-500 line-clamp-2 mb-3">{{ $deck->description }}</p>
                    @endif

                    <div class="flex items-center justify-between text-xs text-gray-400 mb-4">
                        <span>{{ $deck->cards_count }} cards</span>
                        <span>by {{ $deck->user?->name ?? 'Unknown' }}</span>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('user.study.start', $deck) }}"
                           class="flex-1 text-center px-3 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                            Study
                        </a>
                        <a href="{{ route('user.library.show', $deck) }}"
                           class="px-3 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                            View
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 mb-1">No favorites yet</h3>
            <p class="text-gray-500 mb-4">Browse the library and save decks you want to study later.</p>
            <a href="{{ route('user.library.index') }}" class="inline-block px-5 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                Browse Library
            </a>
        </div>
    @endif

</x-user-layout>

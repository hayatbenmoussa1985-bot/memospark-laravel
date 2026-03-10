<x-user-layout :title="$deck->title">

    <div class="max-w-3xl mx-auto">

        {{-- Back link --}}
        <div class="mb-4">
            <a href="{{ route('user.library.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Library
            </a>
        </div>

        {{-- Deck header --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $deck->title }}</h1>
                    @if($deck->description)
                        <p class="text-gray-500 mb-3">{{ $deck->description }}</p>
                    @endif
                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500">
                        <span>by <strong class="text-gray-700">{{ $deck->user?->name ?? 'Unknown' }}</strong></span>
                        <span>&middot;</span>
                        <span>{{ $deck->cards->count() }} cards</span>
                        @if($deck->category)
                            <span>&middot;</span>
                            <span class="px-2 py-0.5 bg-gray-100 text-xs text-gray-600 rounded-full">
                                {{ $deck->category->icon }} {{ $deck->category->name }}
                            </span>
                        @endif
                        <span class="px-2 py-0.5 bg-{{ $deck->difficulty === 'beginner' ? 'emerald' : ($deck->difficulty === 'intermediate' ? 'amber' : 'red') }}-50 text-xs text-{{ $deck->difficulty === 'beginner' ? 'emerald' : ($deck->difficulty === 'intermediate' ? 'amber' : 'red') }}-700 rounded-full capitalize">
                            {{ $deck->difficulty }}
                        </span>
                    </div>

                    @if($deck->average_rating > 0)
                        <div class="flex items-center gap-1 mt-3">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= round($deck->average_rating) ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                            <span class="text-sm text-gray-500 ml-1">{{ number_format($deck->average_rating, 1) }} ({{ $deck->ratings_count }})</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-100">
                @if($hasStarted)
                    <a href="{{ route('user.study.start', $deck) }}"
                       class="px-5 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700">
                        @if($dueCount > 0)
                            Study {{ $dueCount }} Due Cards
                        @else
                            Review Deck
                        @endif
                    </a>
                @else
                    <a href="{{ route('user.study.start', $deck) }}"
                       class="px-5 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700">
                        Start Learning
                    </a>
                @endif

                @if($isFavorited)
                    <form method="POST" action="{{ route('user.library.unfavorite', $deck) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium rounded-xl border border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100">
                            <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            Favorited
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('user.library.favorite', $deck) }}">
                        @csrf
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium rounded-xl border border-gray-300 text-gray-600 hover:bg-gray-50">
                            <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            Add to Favorites
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Cards preview --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-900">Cards ({{ $deck->cards->count() }})</h2>
            </div>

            @if($deck->cards->isNotEmpty())
                <div class="divide-y divide-gray-100">
                    @foreach($deck->cards as $index => $card)
                        <div class="px-6 py-4 hover:bg-gray-50">
                            <div class="flex items-start gap-4">
                                <span class="text-xs font-medium text-gray-400 mt-1 shrink-0">{{ $index + 1 }}</span>
                                <div class="flex-1 min-w-0 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <p class="text-xs font-medium text-gray-400 mb-0.5">Front</p>
                                        <p class="text-sm text-gray-900 line-clamp-2">{{ $card->front_text }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-400 mb-0.5">Back</p>
                                        <p class="text-sm text-gray-600 line-clamp-2">{{ $card->back_text }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center text-gray-400 text-sm">
                    This deck has no cards yet.
                </div>
            @endif
        </div>

    </div>

</x-user-layout>

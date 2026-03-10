<x-user-layout title="Library">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Library</h1>
        <p class="text-sm text-gray-500">Explore public decks created by the community</p>
    </div>

    {{-- Search & Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('user.library.index') }}" class="space-y-4">
            {{-- Search bar --}}
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search decks..."
                       class="w-full pl-10 pr-4 py-2.5 text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            {{-- Filter row --}}
            <div class="flex flex-wrap gap-3 items-end">
                {{-- Category --}}
                <div class="flex-1 min-w-[140px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Category</label>
                    <select name="category" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->icon }} {{ $cat->name }} ({{ $cat->decks_count }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Difficulty --}}
                <div class="flex-1 min-w-[120px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Difficulty</label>
                    <select name="difficulty" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">All Levels</option>
                        <option value="beginner" {{ request('difficulty') === 'beginner' ? 'selected' : '' }}>Beginner</option>
                        <option value="intermediate" {{ request('difficulty') === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="advanced" {{ request('difficulty') === 'advanced' ? 'selected' : '' }}>Advanced</option>
                    </select>
                </div>

                {{-- Sort --}}
                <div class="flex-1 min-w-[120px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Sort by</label>
                    <select name="sort" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="popular" {{ request('sort', 'popular') === 'popular' ? 'selected' : '' }}>Most Popular</option>
                        <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest</option>
                        <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>Highest Rated</option>
                    </select>
                </div>

                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                    Filter
                </button>

                @if(request()->hasAny(['search', 'category', 'difficulty', 'sort']))
                    <a href="{{ route('user.library.index') }}" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Featured decks --}}
    @if($featured->isNotEmpty() && !request()->hasAny(['search', 'category', 'difficulty']))
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                Featured
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($featured as $deck)
                    <a href="{{ route('user.library.show', $deck) }}"
                       class="bg-gradient-to-br from-emerald-50 to-white rounded-xl border border-emerald-200 p-4 hover:shadow-md transition-all group">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span class="text-xs font-medium text-emerald-700">Featured</span>
                        </div>
                        <h3 class="font-semibold text-gray-900 group-hover:text-emerald-700 transition-colors truncate">{{ $deck->title }}</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ $deck->cards_count }} cards</p>
                        @if($deck->category)
                            <span class="inline-block mt-2 px-2 py-0.5 bg-gray-100 text-xs text-gray-600 rounded-full">
                                {{ $deck->category->icon }} {{ $deck->category->name }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Deck grid --}}
    @if($decks->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            @foreach($decks as $deck)
                <a href="{{ route('user.library.show', $deck) }}"
                   class="bg-white rounded-xl border border-gray-200 p-5 hover:border-emerald-300 hover:shadow-sm transition-all group">
                    <div class="flex items-start justify-between mb-3">
                        <h3 class="font-semibold text-gray-900 group-hover:text-emerald-700 transition-colors line-clamp-2">
                            {{ $deck->title }}
                        </h3>
                        @if($deck->average_rating > 0)
                            <div class="flex items-center gap-1 text-xs text-amber-600 shrink-0 ml-2">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                {{ number_format($deck->average_rating, 1) }}
                            </div>
                        @endif
                    </div>

                    @if($deck->description)
                        <p class="text-sm text-gray-500 line-clamp-2 mb-3">{{ $deck->description }}</p>
                    @endif

                    <div class="flex items-center justify-between text-xs text-gray-400">
                        <span>{{ $deck->cards_count }} cards</span>
                        <span>by {{ $deck->user?->name ?? 'Unknown' }}</span>
                    </div>

                    <div class="flex items-center gap-2 mt-3">
                        @if($deck->category)
                            <span class="px-2 py-0.5 bg-gray-100 text-xs text-gray-600 rounded-full">
                                {{ $deck->category->icon }} {{ $deck->category->name }}
                            </span>
                        @endif
                        <span class="px-2 py-0.5 bg-{{ $deck->difficulty === 'beginner' ? 'emerald' : ($deck->difficulty === 'intermediate' ? 'amber' : 'red') }}-50 text-xs text-{{ $deck->difficulty === 'beginner' ? 'emerald' : ($deck->difficulty === 'intermediate' ? 'amber' : 'red') }}-700 rounded-full capitalize">
                            {{ $deck->difficulty }}
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div>
            {{ $decks->links() }}
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 mb-1">No decks found</h3>
            <p class="text-gray-500">Try adjusting your search or filters.</p>
        </div>
    @endif

    {{-- Favorites link --}}
    <div class="mt-6 text-center">
        <a href="{{ route('user.library.favorites') }}" class="text-sm text-emerald-600 hover:underline">
            View my favorite decks &rarr;
        </a>
    </div>

</x-user-layout>

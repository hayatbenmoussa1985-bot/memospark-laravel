@props(['deck', 'showAuthor' => false, 'showActions' => true, 'dueCount' => null])

<div class="bg-white rounded-xl border border-gray-200 p-5 hover:border-emerald-300 hover:shadow-sm transition-all">
    <div class="flex items-start justify-between mb-3">
        <div class="flex-1 min-w-0">
            <h3 class="text-base font-semibold text-gray-900 truncate">{{ $deck->title }}</h3>
            @if($showAuthor && $deck->user)
                <p class="text-xs text-gray-500 mt-0.5">by {{ $deck->user->name }}</p>
            @endif
        </div>
        @if($deck->is_featured)
            <span class="text-amber-500 text-lg ml-2">★</span>
        @endif
    </div>

    @if($deck->description)
        <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ $deck->description }}</p>
    @endif

    {{-- Stats row --}}
    <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
        <span class="flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            {{ $deck->cards_count ?? $deck->cards()->count() }} cards
        </span>
        @if($deck->category)
            <span>{{ $deck->category->icon }} {{ $deck->category->slug }}</span>
        @endif
        @if($deck->difficulty)
            <span class="{{ match($deck->difficulty->value ?? $deck->difficulty) {
                'beginner' => 'text-emerald-600',
                'intermediate' => 'text-amber-600',
                'advanced' => 'text-red-600',
                default => '',
            } }}">{{ ucfirst($deck->difficulty->value ?? $deck->difficulty) }}</span>
        @endif
    </div>

    {{-- Due cards badge --}}
    @if($dueCount !== null && $dueCount > 0)
        <div class="mb-3">
            <span class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-medium">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $dueCount }} cards due
            </span>
        </div>
    @endif

    {{-- Actions --}}
    @if($showActions)
        <div class="flex gap-2">
            {{ $slot }}
        </div>
    @endif
</div>

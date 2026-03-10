<x-admin-layout title="Decks">

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Deck Management</h2>
            <p class="text-sm text-gray-500">{{ $decks->total() }} decks total</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title or author..."
                       class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <select name="visibility" class="text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                <option value="">All Visibility</option>
                <option value="private" {{ request('visibility') === 'private' ? 'selected' : '' }}>Private</option>
                <option value="public" {{ request('visibility') === 'public' ? 'selected' : '' }}>Public</option>
                <option value="library" {{ request('visibility') === 'library' ? 'selected' : '' }}>Library</option>
            </select>
            <select name="category_id" class="text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->icon }} {{ $cat->slug }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-900 text-white text-sm rounded-lg hover:bg-gray-800">Filter</button>
            @if(request()->hasAny(['search', 'visibility', 'category_id']))
                <a href="{{ route('admin.decks.index') }}" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Clear</a>
            @endif
        </form>
    </div>

    <x-admin.table :headers="['Deck', 'Author', 'Cards', 'Visibility', 'Featured', 'Created', 'Actions']">
        @forelse($decks as $deck)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <a href="{{ route('admin.decks.show', $deck) }}" class="text-sm font-medium text-gray-900 hover:text-emerald-600">
                        {{ $deck->title }}
                    </a>
                    @if($deck->category)
                        <p class="text-xs text-gray-500">{{ $deck->category->icon }} {{ $deck->category->slug }}</p>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    {{ $deck->user?->name ?? '—' }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                    {{ $deck->cards_count }}
                </td>
                <td class="px-6 py-4">
                    <x-admin.badge :color="match($deck->visibility->value ?? $deck->visibility) {
                        'private' => 'gray', 'public' => 'blue', 'library' => 'emerald', default => 'gray',
                    }">{{ ucfirst($deck->visibility->value ?? $deck->visibility) }}</x-admin.badge>
                </td>
                <td class="px-6 py-4">
                    <form method="POST" action="{{ route('admin.decks.toggle-featured', $deck) }}">
                        @csrf
                        <button type="submit" class="text-lg {{ $deck->is_featured ? 'text-amber-500' : 'text-gray-300 hover:text-amber-400' }}">
                            ★
                        </button>
                    </form>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    {{ $deck->created_at->format('M d, Y') }}
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.decks.show', $deck) }}" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        <a href="{{ route('admin.decks.edit', $deck) }}" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7"><x-admin.empty-state title="No decks found" icon="search" /></td></tr>
        @endforelse

        <x-slot name="pagination">{{ $decks->links() }}</x-slot>
    </x-admin.table>

</x-admin-layout>

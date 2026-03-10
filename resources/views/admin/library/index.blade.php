<x-admin-layout title="Library">

    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">Library Management</h2>
        <p class="text-sm text-gray-500">Manage categories and public library decks</p>
    </div>

    {{-- Categories --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900">Categories ({{ $categories->count() }})</h3>
            <a href="{{ route('admin.library.categories.create') }}" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                + Add Category
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Icon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Parent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Decks</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($categories as $category)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-lg">{{ $category->icon ?? '📁' }}</td>
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $category->slug }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $category->parentCategory?->slug ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-900">{{ $category->decks_count }}</td>
                            <td class="px-6 py-3">
                                @if($category->is_active)
                                    <x-admin.badge color="green">Active</x-admin.badge>
                                @else
                                    <x-admin.badge color="red">Inactive</x-admin.badge>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-gray-500">{{ $category->sort_order }}</td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.library.categories.edit', $category) }}" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    @if($category->decks_count === 0)
                                        <form method="POST" action="{{ route('admin.library.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?')">
                                            @csrf @method('DELETE')
                                            <button class="text-red-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">No categories yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Library Decks --}}
    <x-admin.table :headers="['Deck', 'Author', 'Cards', 'Category', 'Featured', 'Actions']">
        <x-slot name="toolbar">
            <h3 class="font-semibold text-gray-900">Library Decks ({{ $libraryDecks->total() }})</h3>
        </x-slot>

        @forelse($libraryDecks as $deck)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <a href="{{ route('admin.decks.show', $deck) }}" class="text-sm font-medium text-gray-900 hover:text-emerald-600">{{ $deck->title }}</a>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ $deck->user?->name ?? '—' }}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $deck->cards_count }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ $deck->category?->slug ?? '—' }}</td>
                <td class="px-6 py-4">
                    @if($deck->is_featured) <span class="text-amber-500">★</span> @else <span class="text-gray-300">☆</span> @endif
                </td>
                <td class="px-6 py-4">
                    <a href="{{ route('admin.decks.edit', $deck) }}" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><x-admin.empty-state title="No library decks" message="Library decks will appear here once added." /></td></tr>
        @endforelse

        <x-slot name="pagination">{{ $libraryDecks->links() }}</x-slot>
    </x-admin.table>

</x-admin-layout>

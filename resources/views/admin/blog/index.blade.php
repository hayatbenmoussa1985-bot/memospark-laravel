<x-admin-layout title="Blog">

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Blog Posts</h2>
            <p class="text-sm text-gray-500">{{ $posts->total() }} posts</p>
        </div>
        <a href="{{ route('admin.blog.create') }}" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
            + New Post
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search posts..."
                       class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <select name="status" class="text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                <option value="">All Status</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-900 text-white text-sm rounded-lg hover:bg-gray-800">Filter</button>
        </form>
    </div>

    <x-admin.table :headers="['Title', 'Author', 'Status', 'Published', 'Actions']">
        @forelse($posts as $post)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <a href="{{ route('admin.blog.edit', $post) }}" class="text-sm font-medium text-gray-900 hover:text-emerald-600">
                        {{ $post->title }}
                    </a>
                    <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($post->excerpt, 60) }}</p>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ $post->author?->name ?? '—' }}</td>
                <td class="px-6 py-4">
                    <x-admin.badge :color="match($post->status) {
                        'draft' => 'amber', 'published' => 'green', 'archived' => 'gray', default => 'gray',
                    }">{{ ucfirst($post->status) }}</x-admin.badge>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    {{ $post->published_at?->format('M d, Y') ?? '—' }}
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.blog.edit', $post) }}" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.blog.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="5"><x-admin.empty-state title="No blog posts" message="Create your first blog post." /></td></tr>
        @endforelse

        <x-slot name="pagination">{{ $posts->links() }}</x-slot>
    </x-admin.table>

</x-admin-layout>

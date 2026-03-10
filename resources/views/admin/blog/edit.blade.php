<x-admin-layout :title="'Edit: ' . $post->title">

    <div class="mb-6">
        <a href="{{ route('admin.blog.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Blog
        </a>
    </div>

    <div class="max-w-3xl">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Edit Post</h3>

            <form method="POST" action="{{ route('admin.blog.update', $post) }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $post->title) }}" required
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug', $post->slug) }}" required
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                        <textarea name="excerpt" id="excerpt" rows="2"
                                  class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">{{ old('excerpt', $post->excerpt) }}</textarea>
                    </div>
                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                        <textarea name="content" id="content" rows="12" required
                                  class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 font-mono">{{ old('content', $post->content) }}</textarea>
                    </div>
                    <div>
                        <label for="cover_image" class="block text-sm font-medium text-gray-700 mb-1">Cover Image</label>
                        @if($post->cover_image_path)
                            <p class="text-xs text-gray-500 mb-2">Current: {{ $post->cover_image_path }}</p>
                        @endif
                        <input type="file" name="cover_image" id="cover_image" accept="image/*"
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="status" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="draft" {{ $post->status === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ $post->status === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="archived" {{ $post->status === 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="submit" class="px-6 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">Save</button>
                    <a href="{{ route('admin.blog.index') }}" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</x-admin-layout>

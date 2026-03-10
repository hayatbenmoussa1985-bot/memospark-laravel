<x-admin-layout title="New Category">

    <div class="mb-6">
        <a href="{{ route('admin.library.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Library
        </a>
    </div>

    <div class="max-w-lg">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Create Category</h3>

            <form method="POST" action="{{ route('admin.library.categories.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug') }}" placeholder="e.g. mathematics"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        @error('slug')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="icon" class="block text-sm font-medium text-gray-700 mb-1">Icon (emoji)</label>
                        <input type="text" name="icon" id="icon" value="{{ old('icon') }}" placeholder="📐"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">Parent Category</label>
                        <select name="parent_id" id="parent_id" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">None (root)</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->icon }} {{ $parent->slug }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                        <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked
                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <label for="is_active" class="text-sm text-gray-700">Active</label>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="submit" class="px-6 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">Create</button>
                    <a href="{{ route('admin.library.index') }}" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</x-admin-layout>

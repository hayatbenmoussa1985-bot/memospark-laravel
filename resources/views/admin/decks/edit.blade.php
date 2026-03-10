<x-admin-layout :title="'Edit: ' . $deck->title">

    <div class="mb-6">
        <a href="{{ route('admin.decks.show', $deck) }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Deck
        </a>
    </div>

    <div class="max-w-2xl">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Edit Deck</h3>

            <form method="POST" action="{{ route('admin.decks.update', $deck) }}">
                @csrf @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $deck->title) }}"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" id="description" rows="3"
                                  class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">{{ old('description', $deck->description) }}</textarea>
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category_id" id="category_id" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">No category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $deck->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->icon }} {{ $cat->slug }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="visibility" class="block text-sm font-medium text-gray-700 mb-1">Visibility</label>
                            <select name="visibility" id="visibility" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="private" {{ old('visibility', $deck->visibility->value ?? $deck->visibility) === 'private' ? 'selected' : '' }}>Private</option>
                                <option value="public" {{ old('visibility', $deck->visibility->value ?? $deck->visibility) === 'public' ? 'selected' : '' }}>Public</option>
                                <option value="library" {{ old('visibility', $deck->visibility->value ?? $deck->visibility) === 'library' ? 'selected' : '' }}>Library</option>
                            </select>
                        </div>
                        <div>
                            <label for="difficulty" class="block text-sm font-medium text-gray-700 mb-1">Difficulty</label>
                            <select name="difficulty" id="difficulty" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="beginner" {{ old('difficulty', $deck->difficulty->value ?? $deck->difficulty) === 'beginner' ? 'selected' : '' }}>Beginner</option>
                                <option value="intermediate" {{ old('difficulty', $deck->difficulty->value ?? $deck->difficulty) === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                <option value="advanced" {{ old('difficulty', $deck->difficulty->value ?? $deck->difficulty) === 'advanced' ? 'selected' : '' }}>Advanced</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" id="is_featured" value="1"
                               {{ old('is_featured', $deck->is_featured) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <label for="is_featured" class="text-sm text-gray-700">Featured deck</label>
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit" class="px-6 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">Save</button>
                    <a href="{{ route('admin.decks.show', $deck) }}" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</x-admin-layout>

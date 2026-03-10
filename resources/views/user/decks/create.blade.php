<x-user-layout title="New Deck">

    <div class="mb-4">
        <a href="{{ route('user.decks.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            My Decks
        </a>
    </div>

    <div class="max-w-lg">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Create New Deck</h2>
            <form method="POST" action="{{ route('user.decks.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required placeholder="e.g. French Vocabulary"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" id="description" rows="2" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">{{ old('description') }}</textarea>
                    </div>
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category_id" id="category_id" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">No category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->icon }} {{ $cat->slug }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="difficulty" class="block text-sm font-medium text-gray-700 mb-1">Difficulty</label>
                        <select name="difficulty" id="difficulty" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>
                    <div>
                        <label for="visibility" class="block text-sm font-medium text-gray-700 mb-1">Visibility</label>
                        <select name="visibility" id="visibility" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="private">Private</option>
                            <option value="public">Public</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="submit" class="px-6 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">Create Deck</button>
                    <a href="{{ route('user.decks.index') }}" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</x-user-layout>

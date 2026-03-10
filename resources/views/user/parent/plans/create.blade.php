<x-user-layout title="New Revision Plan">

    <div class="mb-6">
        <a href="{{ route('user.parent.plans.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Plans
        </a>
    </div>

    <div class="max-w-2xl">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Create Revision Plan</h2>

            <form method="POST" action="{{ route('user.parent.plans.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="child_id" class="block text-sm font-medium text-gray-700 mb-1">For Child</label>
                        <select name="child_id" id="child_id" required class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Select child...</option>
                            @foreach($children as $child)
                                <option value="{{ $child->id }}">{{ $child->name }}</option>
                            @endforeach
                        </select>
                        @error('child_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Plan Title</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required placeholder="e.g. Weekly Math Review"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
                        <textarea name="description" id="description" rows="2"
                                  class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" required
                                   class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" required
                                   class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Decks</label>
                        <div class="max-h-64 overflow-y-auto border border-gray-300 rounded-lg p-2 space-y-1">
                            @foreach($availableDecks as $deck)
                                <label class="flex items-center gap-3 p-2 rounded hover:bg-gray-50 cursor-pointer">
                                    <input type="checkbox" name="deck_ids[]" value="{{ $deck->id }}"
                                           class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <p class="text-sm text-gray-900">{{ $deck->title }}</p>
                                        <p class="text-xs text-gray-500">{{ $deck->cards_count }} cards · {{ $deck->category?->slug ?? 'No category' }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('deck_ids')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="px-6 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">Create Plan</button>
                    <a href="{{ route('user.parent.plans.index') }}" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</x-user-layout>

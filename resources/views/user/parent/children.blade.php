<x-user-layout title="My Children">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">My Children</h1>
        <p class="text-sm text-gray-500">Track each child's progress</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($children as $child)
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center text-xl font-bold text-emerald-700">
                        {{ strtoupper(substr($child->name, 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $child->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $child->email }}</p>
                    </div>
                </div>

                <dl class="grid grid-cols-3 gap-4 mb-4">
                    <div class="text-center bg-gray-50 rounded-lg p-3">
                        <dd class="text-xl font-bold text-gray-900">{{ $child->total_decks }}</dd>
                        <dt class="text-xs text-gray-500">Decks</dt>
                    </div>
                    <div class="text-center bg-gray-50 rounded-lg p-3">
                        <dd class="text-xl font-bold text-gray-900">{{ $child->total_sessions }}</dd>
                        <dt class="text-xs text-gray-500">Sessions</dt>
                    </div>
                    <div class="text-center bg-gray-50 rounded-lg p-3">
                        <dd class="text-xl font-bold text-gray-900">{{ $child->total_reviews }}</dd>
                        <dt class="text-xs text-gray-500">Reviews</dt>
                    </div>
                </dl>

                <a href="{{ route('user.parent.children.show', $child) }}" class="block w-full text-center px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                    View Details
                </a>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-xl border border-gray-200 p-8 text-center">
                <p class="text-gray-500">No children linked to your account yet.</p>
            </div>
        @endforelse
    </div>

</x-user-layout>

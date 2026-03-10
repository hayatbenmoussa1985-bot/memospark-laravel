<x-user-layout title="Parent Dashboard">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ auth()->user()->name }}!</h1>
        <p class="text-sm text-gray-500">Overview of your children's learning progress</p>
    </div>

    {{-- Children Overview --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        @forelse($children as $child)
            <a href="{{ route('user.parent.children.show', $child) }}" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-emerald-300 hover:shadow-sm transition-all">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-lg font-bold text-emerald-700">
                        {{ strtoupper(substr($child->name, 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $child->name }}</h3>
                        <p class="text-xs text-gray-500">{{ $child->school_level ?? 'Student' }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 text-center">
                    <div class="bg-gray-50 rounded-lg p-2">
                        <p class="text-lg font-bold text-gray-900">{{ $child->total_decks }}</p>
                        <p class="text-xs text-gray-500">Decks</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-2">
                        <p class="text-lg font-bold {{ $child->due_cards > 0 ? 'text-emerald-600' : 'text-gray-900' }}">{{ $child->due_cards }}</p>
                        <p class="text-xs text-gray-500">Due</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-2">
                        <p class="text-lg font-bold text-gray-900">{{ $child->sessions_this_week }}</p>
                        <p class="text-xs text-gray-500">Sessions</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-2">
                        <p class="text-lg font-bold text-gray-900">🔥 {{ $child->streak }}</p>
                        <p class="text-xs text-gray-500">Streak</p>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full bg-white rounded-xl border border-gray-200 p-8 text-center">
                <p class="text-gray-500">No children linked to your account yet.</p>
                <p class="text-sm text-gray-400 mt-1">Contact support to link your child's account.</p>
            </div>
        @endforelse
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Active Plans --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Active Revision Plans</h3>
                <a href="{{ route('user.parent.plans.create') }}" class="text-sm text-emerald-600 hover:text-emerald-700">+ New Plan</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($activePlans as $plan)
                    <div class="px-6 py-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $plan->title }}</p>
                                <p class="text-xs text-gray-500">{{ $plan->childUser?->name }} — {{ $plan->decks->count() }} decks</p>
                            </div>
                            <span class="text-xs text-gray-400">Until {{ $plan->end_date->format('M d') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-gray-500">No active plans.</div>
                @endforelse
            </div>
        </div>

        {{-- Messages --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">
                    Messages
                    @if($unreadCount > 0)
                        <span class="ml-1 bg-red-500 text-white text-xs font-medium px-2 py-0.5 rounded-full">{{ $unreadCount }}</span>
                    @endif
                </h3>
                <a href="{{ route('user.parent.messages') }}" class="text-sm text-emerald-600 hover:text-emerald-700">View all</a>
            </div>
            <div class="p-6 text-center text-sm text-gray-500">
                <a href="{{ route('user.parent.messages') }}" class="text-emerald-600 hover:underline">Open messages</a> to chat with your children.
            </div>
        </div>
    </div>

</x-user-layout>

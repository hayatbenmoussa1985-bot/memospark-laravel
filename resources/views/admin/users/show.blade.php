<x-admin-layout :title="$user->name">

    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Users
        </a>
    </div>

    {{-- User Header --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center text-2xl font-bold text-emerald-600 shrink-0">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="flex-1">
                <h2 class="text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <x-admin.badge :color="match($user->role->value) {
                        'super_admin' => 'red', 'admin' => 'purple', 'parent' => 'blue',
                        'child' => 'amber', 'learner' => 'emerald', default => 'gray',
                    }">{{ $user->role->label() }}</x-admin.badge>

                    @if($user->is_active)
                        <x-admin.badge color="green">Active</x-admin.badge>
                    @else
                        <x-admin.badge color="red">Inactive</x-admin.badge>
                    @endif

                    @if($user->email_verified_at)
                        <x-admin.badge color="blue">Verified</x-admin.badge>
                    @endif
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 text-sm font-medium bg-gray-900 text-white rounded-lg hover:bg-gray-800">
                    Edit
                </a>
                @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-sm font-medium {{ $user->is_active ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }} rounded-lg">
                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-admin.stat-card title="Decks" :value="$stats['total_decks']" color="emerald" />
        <x-admin.stat-card title="Cards" :value="$stats['total_cards']" color="blue" />
        <x-admin.stat-card title="Study Sessions" :value="$stats['total_sessions']" color="purple" />
        <x-admin.stat-card title="Total Reviews" :value="$stats['total_reviews']" color="amber" />
    </div>

    {{-- Info + Activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- User Info --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">User Information</h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">UUID</dt>
                    <dd class="text-sm text-gray-900 font-mono">{{ $user->uuid }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Locale</dt>
                    <dd class="text-sm text-gray-900">{{ $user->locale ?? 'en' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Timezone</dt>
                    <dd class="text-sm text-gray-900">{{ $user->timezone ?? 'UTC' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">School Level</dt>
                    <dd class="text-sm text-gray-900">{{ $user->school_level ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Date of Birth</dt>
                    <dd class="text-sm text-gray-900">{{ $user->date_of_birth?->format('M d, Y') ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Google ID</dt>
                    <dd class="text-sm text-gray-900">{{ $user->google_id ? 'Linked' : '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Apple ID</dt>
                    <dd class="text-sm text-gray-900">{{ $user->apple_user_id ? 'Linked' : '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Joined</dt>
                    <dd class="text-sm text-gray-900">{{ $user->created_at->format('M d, Y H:i') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Last Login</dt>
                    <dd class="text-sm text-gray-900">{{ $user->last_login_at?->format('M d, Y H:i') ?? 'Never' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Recent Decks --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">Recent Decks</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($user->decks as $deck)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div>
                            <a href="{{ route('admin.decks.show', $deck) }}" class="text-sm font-medium text-gray-900 hover:text-emerald-600">
                                {{ $deck->title }}
                            </a>
                            <p class="text-xs text-gray-500">{{ $deck->cards_count }} cards</p>
                        </div>
                        <x-admin.badge :color="match($deck->visibility->value ?? $deck->visibility) {
                            'private' => 'gray', 'public' => 'blue', 'library' => 'emerald', default => 'gray',
                        }">{{ ucfirst($deck->visibility->value ?? $deck->visibility) }}</x-admin.badge>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-gray-500">No decks yet.</div>
                @endforelse
            </div>
        </div>

        {{-- Subscriptions --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">Subscriptions</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($user->subscriptions as $subscription)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $subscription->plan?->name ?? 'Unknown' }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $subscription->current_period_start?->format('M d') }} — {{ $subscription->current_period_end?->format('M d, Y') }}
                            </p>
                        </div>
                        <x-admin.badge :color="match($subscription->status->value ?? $subscription->status) {
                            'active' => 'green', 'expired' => 'gray', 'cancelled' => 'red', 'trial' => 'blue', default => 'gray',
                        }">{{ ucfirst($subscription->status->value ?? $subscription->status) }}</x-admin.badge>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-gray-500">No subscriptions.</div>
                @endforelse
            </div>
        </div>

        {{-- Badges --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">Badges</h3>
            </div>
            <div class="p-6">
                @if($user->badges->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        @foreach($user->badges as $badge)
                            <div class="flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 border border-amber-200 rounded-full">
                                <span>{{ $badge->icon }}</span>
                                <span class="text-sm font-medium text-amber-800">{{ $badge->name }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center">No badges earned.</p>
                @endif
            </div>
        </div>
    </div>

</x-admin-layout>

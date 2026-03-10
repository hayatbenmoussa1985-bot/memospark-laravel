<x-admin-layout title="Dashboard">

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <x-admin.stat-card
            title="Total Users"
            :value="number_format($totalUsers)"
            :trend="$newUsersThisWeek > 0 ? '+' . $newUsersThisWeek . ' this week' : null"
            :trendUp="true"
            color="blue"
            :href="route('admin.users.index')"
            :icon="'<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z\'/></svg>'" />

        <x-admin.stat-card
            title="Total Decks"
            :value="number_format($totalDecks)"
            color="emerald"
            :href="route('admin.decks.index')"
            :icon="'<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10\'/></svg>'" />

        <x-admin.stat-card
            title="Total Cards"
            :value="number_format($totalCards)"
            color="purple" />

        <x-admin.stat-card
            title="Active Subscriptions"
            :value="number_format($activeSubscriptions)"
            color="amber"
            :href="route('admin.subscriptions.index')" />
    </div>

    {{-- Second row of stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <x-admin.stat-card
            title="Study Sessions Today"
            :value="number_format($studySessionsToday)"
            color="indigo" />

        <x-admin.stat-card
            title="Cards Reviewed Today"
            :value="number_format($cardsReviewedToday)"
            color="emerald" />

        <x-admin.stat-card
            title="Pending Reports"
            :value="number_format($pendingReports)"
            :color="$pendingReports > 0 ? 'red' : 'emerald'"
            :href="route('admin.reports.index')" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Recent Users --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Recent Users</h3>
                <a href="{{ route('admin.users.index') }}" class="text-sm text-emerald-600 hover:text-emerald-700">View all</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentUsers as $user)
                    <div class="px-6 py-3 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-sm font-medium text-gray-600">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                        </div>
                        <x-admin.badge :color="match($user->role->value) {
                            'super_admin' => 'red',
                            'admin' => 'purple',
                            'parent' => 'blue',
                            'child' => 'amber',
                            'learner' => 'emerald',
                            default => 'gray',
                        }">
                            {{ $user->role->label() }}
                        </x-admin.badge>
                        <span class="text-xs text-gray-400">{{ $user->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-gray-500">No users yet.</div>
                @endforelse
            </div>
        </div>

        {{-- Role Distribution --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">User Roles</h3>
            </div>
            <div class="p-6">
                @php
                    $roleColors = [
                        'super_admin' => 'bg-red-500',
                        'admin' => 'bg-purple-500',
                        'parent' => 'bg-blue-500',
                        'child' => 'bg-amber-500',
                        'learner' => 'bg-emerald-500',
                    ];
                    $roleLabels = [
                        'super_admin' => 'Super Admin',
                        'admin' => 'Admin',
                        'parent' => 'Parent',
                        'child' => 'Child',
                        'learner' => 'Learner',
                    ];
                @endphp

                <div class="space-y-4">
                    @foreach($roleDistribution as $role => $count)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">{{ $roleLabels[$role] ?? ucfirst($role) }}</span>
                                <span class="font-medium text-gray-900">{{ $count }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="{{ $roleColors[$role] ?? 'bg-gray-400' }} h-2 rounded-full"
                                     style="width: {{ $totalUsers > 0 ? ($count / $totalUsers * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="bg-white rounded-xl border border-gray-200 lg:col-span-2">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">Recent Activity</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentActivity as $activity)
                    <div class="px-6 py-3 flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-700">
                                <span class="font-medium">{{ $activity->user?->name ?? 'Unknown' }}</span>
                                — {{ str_replace('_', ' ', $activity->activity_type) }}
                            </p>
                        </div>
                        <span class="text-xs text-gray-400 whitespace-nowrap">{{ $activity->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-gray-500">No recent activity.</div>
                @endforelse
            </div>
        </div>

    </div>

</x-admin-layout>

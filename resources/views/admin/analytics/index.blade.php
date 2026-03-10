<x-admin-layout title="Analytics">

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Analytics</h2>
            <p class="text-sm text-gray-500">Overview of platform activity</p>
        </div>
        <form method="GET" class="flex gap-2">
            <select name="period" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                <option value="7" {{ $period == '7' ? 'selected' : '' }}>Last 7 days</option>
                <option value="14" {{ $period == '14' ? 'selected' : '' }}>Last 14 days</option>
                <option value="30" {{ $period == '30' ? 'selected' : '' }}>Last 30 days</option>
                <option value="90" {{ $period == '90' ? 'selected' : '' }}>Last 90 days</option>
            </select>
        </form>
    </div>

    {{-- Key Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <x-admin.stat-card
            title="New Users"
            :value="array_sum($userRegistrations)"
            color="blue" />
        <x-admin.stat-card
            title="Study Sessions"
            :value="array_sum($studySessions)"
            color="emerald" />
        <x-admin.stat-card
            title="Avg. Session Duration"
            :value="$avgSessionDuration ? round($avgSessionDuration / 60, 1) . ' min' : '—'"
            color="purple" />
        <x-admin.stat-card
            title="Avg. Accuracy"
            :value="$avgAccuracy ? round($avgAccuracy, 1) . '%' : '—'"
            color="amber" />
    </div>

    {{-- Charts data as tables --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- User Registrations --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">User Registrations</h3>
            @if(!empty($userRegistrations))
                <div class="space-y-2">
                    @foreach($userRegistrations as $date => $count)
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-500 w-20">{{ \Carbon\Carbon::parse($date)->format('M d') }}</span>
                            <div class="flex-1 bg-gray-100 rounded-full h-4">
                                <div class="bg-blue-500 h-4 rounded-full" style="width: {{ max($userRegistrations) > 0 ? ($count / max($userRegistrations) * 100) : 0 }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900 w-8 text-right">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500">No data for this period.</p>
            @endif
        </div>

        {{-- Study Sessions --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Study Sessions</h3>
            @if(!empty($studySessions))
                <div class="space-y-2">
                    @foreach($studySessions as $date => $count)
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-500 w-20">{{ \Carbon\Carbon::parse($date)->format('M d') }}</span>
                            <div class="flex-1 bg-gray-100 rounded-full h-4">
                                <div class="bg-emerald-500 h-4 rounded-full" style="width: {{ max($studySessions) > 0 ? ($count / max($studySessions) * 100) : 0 }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900 w-8 text-right">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500">No data for this period.</p>
            @endif
        </div>
    </div>

    {{-- Top Users & Top Decks --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">Most Active Users</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($topUsers as $index => $user)
                    <div class="px-6 py-3 flex items-center gap-3">
                        <span class="text-sm font-bold text-gray-400 w-6">{{ $index + 1 }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</p>
                        </div>
                        <span class="text-sm text-emerald-600 font-medium">{{ $user->study_sessions_count }} sessions</span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-gray-500">No data yet.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">Most Popular Decks</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($topDecks as $index => $deck)
                    <div class="px-6 py-3 flex items-center gap-3">
                        <span class="text-sm font-bold text-gray-400 w-6">{{ $index + 1 }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $deck->title }}</p>
                        </div>
                        <span class="text-sm text-emerald-600 font-medium">{{ $deck->study_sessions_count }} sessions</span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-gray-500">No data yet.</div>
                @endforelse
            </div>
        </div>

    </div>

</x-admin-layout>

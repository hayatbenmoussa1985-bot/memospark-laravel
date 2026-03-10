<x-admin-layout title="Subscriptions">

    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">Subscription Management</h2>
        <p class="text-sm text-gray-500">Plans, active subscriptions, and revenue overview</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-admin.stat-card title="Active Subscriptions" :value="number_format($totalActive)" color="emerald" />
        <x-admin.stat-card title="Est. Monthly Revenue" :value="'$' . number_format($monthlyRevenue, 2)" color="blue" />
        <x-admin.stat-card title="Total Plans" :value="$plans->count()" color="purple" />
    </div>

    {{-- Plans --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-900">Subscription Plans</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active Users</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($plans as $plan)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $plan->name }}
                                @if(!$plan->is_active) <x-admin.badge color="red">Inactive</x-admin.badge> @endif
                            </td>
                            <td class="px-6 py-4 text-gray-900">${{ number_format($plan->price, 2) }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $plan->duration_days > 0 ? $plan->duration_days . ' days' : 'Forever' }}</td>
                            <td class="px-6 py-4 text-emerald-600 font-medium">{{ $plan->active_subscriptions_count }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $plan->subscriptions_count }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.subscriptions.plans.edit', $plan) }}" class="text-sm text-emerald-600 hover:text-emerald-700">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Subscriptions --}}
    <x-admin.table :headers="['User', 'Plan', 'Status', 'Period', 'Created']">
        <x-slot name="toolbar">
            <h3 class="font-semibold text-gray-900">Recent Subscriptions</h3>
        </x-slot>

        @forelse($recentSubscriptions as $sub)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <a href="{{ route('admin.users.show', $sub->user) }}" class="text-sm font-medium text-gray-900 hover:text-emerald-600">
                        {{ $sub->user?->name ?? '—' }}
                    </a>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ $sub->plan?->name ?? '—' }}</td>
                <td class="px-6 py-4">
                    <x-admin.badge :color="match($sub->status->value ?? $sub->status) {
                        'active' => 'green', 'expired' => 'gray', 'cancelled' => 'red', 'trial' => 'blue', default => 'gray',
                    }">{{ ucfirst($sub->status->value ?? $sub->status) }}</x-admin.badge>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    {{ $sub->current_period_start?->format('M d') }} — {{ $sub->current_period_end?->format('M d, Y') }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ $sub->created_at->format('M d, Y') }}</td>
            </tr>
        @empty
            <tr><td colspan="5"><x-admin.empty-state title="No subscriptions yet" /></td></tr>
        @endforelse

        <x-slot name="pagination">{{ $recentSubscriptions->links() }}</x-slot>
    </x-admin.table>

</x-admin-layout>

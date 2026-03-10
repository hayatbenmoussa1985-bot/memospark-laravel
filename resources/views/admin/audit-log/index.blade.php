<x-admin-layout title="Audit Log">

    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">Audit Log</h2>
        <p class="text-sm text-gray-500">Track all admin actions and system changes</p>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="action" class="text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                <option value="">All Actions</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                        {{ str_replace('_', ' ', $action) }}
                    </option>
                @endforeach
            </select>
            <select name="target_type" class="text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                <option value="">All Targets</option>
                @foreach($targetTypes as $type)
                    <option value="{{ $type }}" {{ request('target_type') === $type ? 'selected' : '' }}>
                        {{ ucfirst($type) }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" placeholder="From"
                   class="text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
            <input type="date" name="to" value="{{ request('to') }}" placeholder="To"
                   class="text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
            <button type="submit" class="px-4 py-2 bg-gray-900 text-white text-sm rounded-lg hover:bg-gray-800">Filter</button>
            @if(request()->hasAny(['action', 'target_type', 'from', 'to']))
                <a href="{{ route('admin.audit-log.index') }}" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Clear</a>
            @endif
        </form>
    </div>

    <x-admin.table :headers="['Time', 'User', 'Action', 'Target', 'IP', 'Details']">
        @forelse($logs as $log)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                    {{ $log->created_at->format('M d H:i') }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ $log->user?->name ?? '—' }}</td>
                <td class="px-6 py-4">
                    <x-admin.badge color="blue">{{ str_replace('_', ' ', $log->action) }}</x-admin.badge>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    @if($log->target_type)
                        {{ ucfirst($log->target_type) }} #{{ $log->target_id }}
                    @else
                        —
                    @endif
                </td>
                <td class="px-6 py-4 text-sm text-gray-400 font-mono">{{ $log->ip_address ?? '—' }}</td>
                <td class="px-6 py-4">
                    <a href="{{ route('admin.audit-log.show', $log) }}" class="text-sm text-emerald-600 hover:text-emerald-700">View</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><x-admin.empty-state title="No audit logs" message="Actions will appear here as admins use the system." /></td></tr>
        @endforelse

        <x-slot name="pagination">{{ $logs->links() }}</x-slot>
    </x-admin.table>

</x-admin-layout>

<x-admin-layout title="Reports">

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Content Reports</h2>
            <p class="text-sm text-gray-500">{{ $pendingCount }} pending reports</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="status" class="text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
            </select>
            <select name="type" class="text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                <option value="">All Types</option>
                <option value="deck" {{ request('type') === 'deck' ? 'selected' : '' }}>Deck</option>
                <option value="card" {{ request('type') === 'card' ? 'selected' : '' }}>Card</option>
                <option value="user" {{ request('type') === 'user' ? 'selected' : '' }}>User</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-900 text-white text-sm rounded-lg hover:bg-gray-800">Filter</button>
            @if(request()->hasAny(['status', 'type']))
                <a href="{{ route('admin.reports.index') }}" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Clear</a>
            @endif
        </form>
    </div>

    <x-admin.table :headers="['Reporter', 'Type', 'Reason', 'Status', 'Date', 'Actions']">
        @forelse($reports as $report)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-900">{{ $report->reporter?->name ?? '—' }}</td>
                <td class="px-6 py-4">
                    <x-admin.badge :color="match($report->reportable_type) {
                        'deck' => 'blue', 'card' => 'purple', 'user' => 'amber', default => 'gray',
                    }">{{ ucfirst($report->reportable_type) }}</x-admin.badge>
                </td>
                <td class="px-6 py-4 text-sm text-gray-700">{{ Str::limit($report->reason, 50) }}</td>
                <td class="px-6 py-4">
                    <x-admin.badge :color="match($report->status) {
                        'pending' => 'amber', 'reviewed' => 'blue', 'resolved' => 'green', 'dismissed' => 'gray', default => 'gray',
                    }">{{ ucfirst($report->status) }}</x-admin.badge>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ $report->created_at->format('M d, Y') }}</td>
                <td class="px-6 py-4">
                    <a href="{{ route('admin.reports.show', $report) }}" class="text-sm text-emerald-600 hover:text-emerald-700">Review</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><x-admin.empty-state title="No reports" message="No content reports at this time." /></td></tr>
        @endforelse

        <x-slot name="pagination">{{ $reports->links() }}</x-slot>
    </x-admin.table>

</x-admin-layout>

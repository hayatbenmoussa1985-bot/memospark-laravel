<x-admin-layout title="Notifications">

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Notifications</h2>
            <p class="text-sm text-gray-500">{{ $totalSent }} sent total, {{ $unreadCount }} unread</p>
        </div>
        <a href="{{ route('admin.notifications.create') }}" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
            + Send Notification
        </a>
    </div>

    <x-admin.table :headers="['User', 'Title', 'Type', 'Read', 'Sent']">
        @forelse($recentNotifications as $notification)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-900">{{ $notification->user?->name ?? '—' }}</td>
                <td class="px-6 py-4">
                    <p class="text-sm font-medium text-gray-900">{{ $notification->title }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($notification->message, 80) }}</p>
                </td>
                <td class="px-6 py-4">
                    <x-admin.badge color="blue">{{ $notification->type }}</x-admin.badge>
                </td>
                <td class="px-6 py-4">
                    @if($notification->read_at)
                        <x-admin.badge color="green">Read</x-admin.badge>
                    @else
                        <x-admin.badge color="amber">Unread</x-admin.badge>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ $notification->created_at->diffForHumans() }}</td>
            </tr>
        @empty
            <tr><td colspan="5"><x-admin.empty-state title="No notifications sent" /></td></tr>
        @endforelse

        <x-slot name="pagination">{{ $recentNotifications->links() }}</x-slot>
    </x-admin.table>

</x-admin-layout>

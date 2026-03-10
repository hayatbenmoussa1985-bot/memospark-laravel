<x-admin-layout title="Users">

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">User Management</h2>
            <p class="text-sm text-gray-500">{{ $users->total() }} users total</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..."
                       class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <select name="role" class="text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                <option value="">All Roles</option>
                @foreach(\App\Enums\UserRole::cases() as $role)
                    <option value="{{ $role->value }}" {{ request('role') === $role->value ? 'selected' : '' }}>
                        {{ $role->label() }}
                    </option>
                @endforeach
            </select>
            <select name="active" class="text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                <option value="">All Status</option>
                <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-900 text-white text-sm rounded-lg hover:bg-gray-800">
                Filter
            </button>
            @if(request()->hasAny(['search', 'role', 'active']))
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <x-admin.table :headers="['User', 'Role', 'Status', 'Joined', 'Last Login', 'Actions']">
        @forelse($users as $user)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-sm font-medium text-gray-600 shrink-0">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <a href="{{ route('admin.users.show', $user) }}" class="text-sm font-medium text-gray-900 hover:text-emerald-600">
                                {{ $user->name }}
                            </a>
                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
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
                </td>
                <td class="px-6 py-4">
                    @if($user->is_active)
                        <x-admin.badge color="green">Active</x-admin.badge>
                    @else
                        <x-admin.badge color="red">Inactive</x-admin.badge>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    {{ $user->created_at->format('M d, Y') }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.users.show', $user) }}" class="text-gray-400 hover:text-gray-600" title="View">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        <a href="{{ route('admin.users.edit', $user) }}" class="text-gray-400 hover:text-gray-600" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    <x-admin.empty-state title="No users found" message="Try adjusting your search filters." icon="search" />
                </td>
            </tr>
        @endforelse

        <x-slot name="pagination">
            {{ $users->links() }}
        </x-slot>
    </x-admin.table>

</x-admin-layout>

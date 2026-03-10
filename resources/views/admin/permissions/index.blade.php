<x-admin-layout title="Permissions">

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Admin Permissions</h2>
            <p class="text-sm text-gray-500">Manage admin users and their permissions</p>
        </div>
    </div>

    {{-- Promote User to Admin --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h3 class="font-semibold text-gray-900 mb-4">Promote User to Admin</h3>
        <form method="POST" action="{{ route('admin.permissions.promote') }}" class="flex gap-3">
            @csrf
            <input type="number" name="user_id" placeholder="User ID" required
                   class="w-40 text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
            <button type="submit" class="px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700"
                    onclick="return confirm('Promote this user to admin?')">
                Promote
            </button>
        </form>
        @error('user_id')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Admins List --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-900">Admin Users ({{ $admins->count() }})</h3>
        </div>

        @forelse($admins as $admin)
            <div class="px-6 py-4 border-b border-gray-100 last:border-0">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-lg font-bold text-purple-600">
                            {{ strtoupper(substr($admin->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $admin->name }}</p>
                            <p class="text-sm text-gray-500">{{ $admin->email }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.permissions.edit', $admin) }}" class="px-3 py-1.5 text-sm text-emerald-600 border border-emerald-200 rounded-lg hover:bg-emerald-50">
                            Edit Permissions
                        </a>
                        <form method="POST" action="{{ route('admin.permissions.demote', $admin) }}" onsubmit="return confirm('Demote {{ $admin->name }}?')">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50">
                                Demote
                            </button>
                        </form>
                    </div>
                </div>
                {{-- Current Permissions --}}
                <div class="flex flex-wrap gap-1.5 pl-13">
                    @forelse($admin->adminPermissions as $ap)
                        <x-admin.badge color="indigo">{{ str_replace('_', ' ', $ap->permission_slug) }}</x-admin.badge>
                    @empty
                        <span class="text-xs text-gray-400">No permissions assigned</span>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="px-6 py-8 text-center text-sm text-gray-500">No admin users besides super admin.</div>
        @endforelse
    </div>

    {{-- All Available Permissions Reference --}}
    <div class="bg-white rounded-xl border border-gray-200 mt-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-900">Available Permissions</h3>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($permissions->groupBy('group') as $group => $perms)
                <div class="px-6 py-3">
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-2">{{ $group }}</p>
                    <div class="space-y-1">
                        @foreach($perms as $perm)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-900">{{ $perm->name }}</span>
                                <span class="text-xs text-gray-400 font-mono">{{ $perm->slug }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</x-admin-layout>

<x-admin-layout :title="'Permissions: ' . $user->name">

    <div class="mb-6">
        <a href="{{ route('admin.permissions.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Permissions
        </a>
    </div>

    <div class="max-w-lg">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-lg font-bold text-purple-600">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.permissions.update', $user) }}">
                @csrf @method('PUT')

                <p class="text-sm text-gray-600 mb-4">Select the permissions this admin should have:</p>

                <div class="space-y-4">
                    @foreach($permissions->groupBy('group') as $group => $perms)
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase mb-2">{{ $group }}</p>
                            <div class="space-y-2">
                                @foreach($perms as $perm)
                                    <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm->slug }}"
                                               {{ in_array($perm->slug, $userPermissionSlugs) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $perm->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $perm->description }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="px-6 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                        Save Permissions
                    </button>
                    <a href="{{ route('admin.permissions.index') }}" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</x-admin-layout>

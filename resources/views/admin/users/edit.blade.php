<x-admin-layout :title="'Edit ' . $user->name">

    <div class="mb-6">
        <a href="{{ route('admin.users.show', $user) }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to {{ $user->name }}
        </a>
    </div>

    <div class="max-w-2xl">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Edit User</h3>

            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Role --}}
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select name="role" id="role" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                            @foreach(\App\Enums\UserRole::cases() as $role)
                                <option value="{{ $role->value }}" {{ old('role', $user->role->value) === $role->value ? 'selected' : '' }}>
                                    {{ $role->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Active --}}
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <label for="is_active" class="text-sm text-gray-700">Active account</label>
                    </div>

                    {{-- Locale --}}
                    <div>
                        <label for="locale" class="block text-sm font-medium text-gray-700 mb-1">Locale</label>
                        <select name="locale" id="locale" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="en" {{ old('locale', $user->locale) === 'en' ? 'selected' : '' }}>English</option>
                            <option value="fr" {{ old('locale', $user->locale) === 'fr' ? 'selected' : '' }}>French</option>
                            <option value="es" {{ old('locale', $user->locale) === 'es' ? 'selected' : '' }}>Spanish</option>
                            <option value="ar" {{ old('locale', $user->locale) === 'ar' ? 'selected' : '' }}>Arabic</option>
                        </select>
                    </div>

                    {{-- School Level --}}
                    <div>
                        <label for="school_level" class="block text-sm font-medium text-gray-700 mb-1">School Level</label>
                        <input type="text" name="school_level" id="school_level" value="{{ old('school_level', $user->school_level) }}"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit" class="px-6 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                        Save Changes
                    </button>
                    <a href="{{ route('admin.users.show', $user) }}" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        {{-- Danger Zone --}}
        <div class="bg-white rounded-xl border border-red-200 p-6 mt-6">
            <h3 class="text-lg font-semibold text-red-600 mb-4">Danger Zone</h3>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-900">Reset Password</p>
                    <p class="text-xs text-gray-500">Set password to default temporary password.</p>
                </div>
                <form method="POST" action="{{ route('admin.users.reset-password', $user) }}"
                      onsubmit="return confirm('Reset password for {{ $user->name }}?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100">
                        Reset Password
                    </button>
                </form>
            </div>
        </div>
    </div>

</x-admin-layout>

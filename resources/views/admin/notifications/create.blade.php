<x-admin-layout title="Send Notification">

    <div class="mb-6">
        <a href="{{ route('admin.notifications.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Notifications
        </a>
    </div>

    <div class="max-w-2xl">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Send Notification</h3>

            <form method="POST" action="{{ route('admin.notifications.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                        <textarea name="message" id="message" rows="4" required
                                  class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">{{ old('message') }}</textarea>
                        @error('message')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type" id="type" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="system">System</option>
                            <option value="announcement">Announcement</option>
                            <option value="promotion">Promotion</option>
                            <option value="reminder">Reminder</option>
                        </select>
                    </div>
                    <div>
                        <label for="target" class="block text-sm font-medium text-gray-700 mb-1">Send To</label>
                        <select name="target" id="target" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                                x-data x-on:change="$refs.specificUsers.classList.toggle('hidden', $event.target.value !== 'specific')">
                            <option value="all">All Users</option>
                            @foreach($roles as $role)
                                @if($role !== 'all')
                                    <option value="{{ $role }}">{{ ucfirst($role) }}s only</option>
                                @endif
                            @endforeach
                            <option value="specific">Specific Users</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="submit" class="px-6 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700"
                            onclick="return confirm('Send this notification?')">
                        Send Notification
                    </button>
                    <a href="{{ route('admin.notifications.index') }}" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</x-admin-layout>

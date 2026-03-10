<x-user-layout title="Profile">

    <div class="max-w-2xl mx-auto">

        <h1 class="text-2xl font-bold text-gray-900 mb-6">My Profile</h1>

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_decks'] }}</p>
                <p class="text-xs text-gray-500">Decks</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-emerald-600">{{ number_format($stats['total_reviews']) }}</p>
                <p class="text-xs text-gray-500">Total Reviews</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-amber-600">{{ $stats['badges_count'] }}</p>
                <p class="text-xs text-gray-500">Badges</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-sm font-semibold text-gray-900">{{ $stats['member_since'] }}</p>
                <p class="text-xs text-gray-500">Member Since</p>
            </div>
        </div>

        {{-- Badges --}}
        @if($badges->isNotEmpty())
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4">My Badges</h2>
                <div class="flex flex-wrap gap-3">
                    @foreach($badges as $badge)
                        <div class="flex items-center gap-2 px-3 py-2 rounded-lg border" style="background-color: {{ $badge->color }}15; border-color: {{ $badge->color }}40;">
                            <span class="text-lg">{{ $badge->icon }}</span>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $badge->name }}</p>
                                @if($badge->description)
                                    <p class="text-xs text-gray-500">{{ $badge->description }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Edit profile form --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Edit Profile</h2>

            <form method="POST" action="{{ route('user.profile.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                           class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    @error('name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                           class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    @error('email')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="school_level" class="block text-sm font-medium text-gray-700 mb-1">School Level</label>
                        <input type="text" name="school_level" id="school_level" value="{{ old('school_level', $user->school_level) }}"
                               placeholder="e.g. High School, University..."
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                        <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="locale" class="block text-sm font-medium text-gray-700 mb-1">Language</label>
                        <select name="locale" id="locale" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="en" {{ old('locale', $user->locale) === 'en' ? 'selected' : '' }}>English</option>
                            <option value="fr" {{ old('locale', $user->locale) === 'fr' ? 'selected' : '' }}>Fran&ccedil;ais</option>
                            <option value="es" {{ old('locale', $user->locale) === 'es' ? 'selected' : '' }}>Espa&ntilde;ol</option>
                            <option value="ar" {{ old('locale', $user->locale) === 'ar' ? 'selected' : '' }}>العربية</option>
                        </select>
                    </div>
                    <div>
                        <label for="timezone" class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                        <select name="timezone" id="timezone" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                            @foreach(['UTC', 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'Europe/London', 'Europe/Paris', 'Europe/Berlin', 'Asia/Tokyo', 'Asia/Shanghai', 'Asia/Dubai', 'Africa/Cairo', 'Africa/Casablanca'] as $tz)
                                <option value="{{ $tz }}" {{ old('timezone', $user->timezone) === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- Change password --}}
        @if($user->password)
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Change Password</h2>

                <form method="POST" action="{{ route('user.profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                        <input type="password" name="current_password" id="current_password" required
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        @error('current_password')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input type="password" name="password" id="password" required
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        @error('password')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="px-5 py-2.5 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800">
                            Change Password
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-900 mb-2">Password</h2>
                <p class="text-sm text-gray-500">You signed in with a social provider (Google/Apple). No password is set.</p>
            </div>
        @endif

        {{-- Account info --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Account Info</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Role</dt>
                    <dd class="font-medium text-gray-900 capitalize">{{ str_replace('_', ' ', $user->role->value ?? $user->role) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Email verified</dt>
                    <dd class="font-medium {{ $user->email_verified_at ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ $user->email_verified_at ? 'Yes' : 'Not yet' }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Last login</dt>
                    <dd class="text-gray-900">{{ $user->last_login_at?->diffForHumans() ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Google linked</dt>
                    <dd class="text-gray-900">{{ $user->google_id ? 'Yes' : 'No' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Apple linked</dt>
                    <dd class="text-gray-900">{{ $user->apple_user_id ? 'Yes' : 'No' }}</dd>
                </div>
            </dl>
        </div>

    </div>

</x-user-layout>

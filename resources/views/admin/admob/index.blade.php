<x-admin-layout title="AdMob Configuration">

    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">AdMob Configuration</h2>
        <p class="text-sm text-gray-500">Manage advertising settings for the mobile application</p>
    </div>

    <form method="POST" action="{{ route('admin.admob.update') }}">
        @csrf @method('PUT')

        <div class="space-y-6 max-w-3xl">

            {{-- Global Settings --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Global Settings
                </h3>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">Enable Ads</p>
                            <p class="text-sm text-gray-500">Toggle advertising across the mobile app</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="enabled" value="0">
                            <input type="checkbox" name="enabled" value="1" class="sr-only peer"
                                   {{ $config['enabled'] ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Publisher ID</label>
                        <input type="text" name="publisher_id" value="{{ $config['publisher_id'] }}"
                               placeholder="pub-xxxxxxxxxxxxxxxx"
                               class="w-full text-sm font-mono border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>
            </div>

            {{-- Android Ad Units --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.6 11.48l1.78-3.08c.1-.18.04-.4-.13-.51-.18-.1-.4-.04-.51.13l-1.8 3.11c-1.37-.63-2.9-.98-4.54-.98-1.64 0-3.17.36-4.54.98L6.06 8.02c-.1-.18-.33-.23-.51-.13-.18.1-.23.33-.13.51l1.78 3.08C3.7 13.4 1.26 17.28 1 17.28h22c-.26 0-2.7-3.88-5.4-5.8zM7 15.25c-.69 0-1.25-.56-1.25-1.25s.56-1.25 1.25-1.25 1.25.56 1.25 1.25-.56 1.25-1.25 1.25zm10 0c-.69 0-1.25-.56-1.25-1.25s.56-1.25 1.25-1.25 1.25.56 1.25 1.25-.56 1.25-1.25 1.25z"/>
                    </svg>
                    Android Ad Units
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">App ID</label>
                        <input type="text" name="android_app_id" value="{{ $config['android']['app_id'] }}"
                               placeholder="ca-app-pub-xxxxxxxxxxxxxxxx~xxxxxxxxxx"
                               class="w-full text-sm font-mono border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Banner ID</label>
                            <input type="text" name="android_banner_id" value="{{ $config['android']['banner_id'] }}"
                                   placeholder="ca-app-pub-xxx/xxx"
                                   class="w-full text-sm font-mono border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Interstitial ID</label>
                            <input type="text" name="android_interstitial_id" value="{{ $config['android']['interstitial_id'] }}"
                                   placeholder="ca-app-pub-xxx/xxx"
                                   class="w-full text-sm font-mono border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rewarded ID</label>
                            <input type="text" name="android_rewarded_id" value="{{ $config['android']['rewarded_id'] }}"
                                   placeholder="ca-app-pub-xxx/xxx"
                                   class="w-full text-sm font-mono border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Native ID</label>
                            <input type="text" name="android_native_id" value="{{ $config['android']['native_id'] }}"
                                   placeholder="ca-app-pub-xxx/xxx"
                                   class="w-full text-sm font-mono border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>
                </div>
            </div>

            {{-- iOS Ad Units --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-800" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                    </svg>
                    iOS Ad Units
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">App ID</label>
                        <input type="text" name="ios_app_id" value="{{ $config['ios']['app_id'] }}"
                               placeholder="ca-app-pub-xxxxxxxxxxxxxxxx~xxxxxxxxxx"
                               class="w-full text-sm font-mono border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Banner ID</label>
                            <input type="text" name="ios_banner_id" value="{{ $config['ios']['banner_id'] }}"
                                   placeholder="ca-app-pub-xxx/xxx"
                                   class="w-full text-sm font-mono border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Interstitial ID</label>
                            <input type="text" name="ios_interstitial_id" value="{{ $config['ios']['interstitial_id'] }}"
                                   placeholder="ca-app-pub-xxx/xxx"
                                   class="w-full text-sm font-mono border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rewarded ID</label>
                            <input type="text" name="ios_rewarded_id" value="{{ $config['ios']['rewarded_id'] }}"
                                   placeholder="ca-app-pub-xxx/xxx"
                                   class="w-full text-sm font-mono border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Native ID</label>
                            <input type="text" name="ios_native_id" value="{{ $config['ios']['native_id'] }}"
                                   placeholder="ca-app-pub-xxx/xxx"
                                   class="w-full text-sm font-mono border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Frequency Settings --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Frequency Rules
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Interstitial every N sessions</label>
                        <input type="number" name="interstitial_every_n_sessions"
                               value="{{ $config['frequency']['interstitial_every_n_sessions'] }}"
                               min="1" max="100"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        <p class="text-xs text-gray-400 mt-1">Show interstitial ad every N study sessions</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Min seconds between ads</label>
                        <input type="number" name="min_time_between_ads_seconds"
                               value="{{ $config['frequency']['min_time_between_ads_seconds'] }}"
                               min="10" max="3600"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        <p class="text-xs text-gray-400 mt-1">Minimum cooldown between two ads</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Native every N items</label>
                        <input type="number" name="native_every_n_items"
                               value="{{ $config['frequency']['native_every_n_items'] }}"
                               min="1" max="100"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        <p class="text-xs text-gray-400 mt-1">Show native ad every N items in lists</p>
                    </div>
                </div>
            </div>

            {{-- Compliance --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Compliance & Safety
                </h3>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900 text-sm">COPPA Compliant</p>
                            <p class="text-xs text-gray-500">Children's Online Privacy Protection Act</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="coppa_compliant" value="0">
                            <input type="checkbox" name="coppa_compliant" value="1" class="sr-only peer"
                                   {{ $config['compliance']['coppa_compliant'] ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900 text-sm">GDPR Compliant</p>
                            <p class="text-xs text-gray-500">EU General Data Protection Regulation</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="gdpr_compliant" value="0">
                            <input type="checkbox" name="gdpr_compliant" value="1" class="sr-only peer"
                                   {{ $config['compliance']['gdpr_compliant'] ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Ad Content Rating</label>
                        <select name="max_ad_content_rating"
                                class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="G" {{ $config['compliance']['max_ad_content_rating'] === 'G' ? 'selected' : '' }}>G — General audiences</option>
                            <option value="PG" {{ $config['compliance']['max_ad_content_rating'] === 'PG' ? 'selected' : '' }}>PG — Parental guidance</option>
                            <option value="T" {{ $config['compliance']['max_ad_content_rating'] === 'T' ? 'selected' : '' }}>T — Teen</option>
                            <option value="MA" {{ $config['compliance']['max_ad_content_rating'] === 'MA' ? 'selected' : '' }}>MA — Mature audiences</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-4">
                <button type="submit"
                        class="px-6 py-2.5 bg-emerald-600 text-white font-medium text-sm rounded-lg hover:bg-emerald-700 transition">
                    Save Configuration
                </button>
                <p class="text-sm text-gray-500">Changes take effect immediately for mobile apps.</p>
            </div>

        </div>
    </form>

</x-admin-layout>

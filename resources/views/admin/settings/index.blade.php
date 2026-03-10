<x-admin-layout title="Settings">

    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">Application Settings</h2>
        <p class="text-sm text-gray-500">Configure your MemoSpark application</p>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf @method('PUT')

        <div class="space-y-6 max-w-2xl">

            {{-- General --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4">General</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">App Name</label>
                        <input type="text" name="settings[app_name]" value="{{ $settings['general']['app_name'] }}"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">App Description</label>
                        <input type="text" name="settings[app_description]" value="{{ $settings['general']['app_description'] }}"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="settings[maintenance_mode]" value="0">
                        <input type="checkbox" name="settings[maintenance_mode]" value="1"
                               {{ $settings['general']['maintenance_mode'] ? 'checked' : '' }}
                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <label class="text-sm text-gray-700">Maintenance Mode</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="settings[registration_enabled]" value="0">
                        <input type="checkbox" name="settings[registration_enabled]" value="1"
                               {{ $settings['general']['registration_enabled'] ? 'checked' : '' }}
                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <label class="text-sm text-gray-700">Registration Enabled</label>
                    </div>
                </div>
            </div>

            {{-- AI --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4">AI Configuration</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">OpenRouter API Key</label>
                        <input type="password" name="settings[openrouter_api_key]" value="{{ $settings['ai']['openrouter_api_key'] }}"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">OpenRouter Model</label>
                        <input type="text" name="settings[openrouter_model]" value="{{ $settings['ai']['openrouter_model'] }}"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fal.ai API Key</label>
                        <input type="password" name="settings[fal_ai_api_key]" value="{{ $settings['ai']['fal_ai_api_key'] }}"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="settings[ai_generation_enabled]" value="0">
                        <input type="checkbox" name="settings[ai_generation_enabled]" value="1"
                               {{ $settings['ai']['ai_generation_enabled'] ? 'checked' : '' }}
                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <label class="text-sm text-gray-700">AI Generation Enabled</label>
                    </div>
                </div>
            </div>

            {{-- n8n --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4">n8n Integration</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">n8n Webhook URL</label>
                        <input type="url" name="settings[n8n_webhook_url]" value="{{ $settings['n8n']['n8n_webhook_url'] }}"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">n8n API Key</label>
                        <input type="password" name="settings[n8n_api_key]" value="{{ $settings['n8n']['n8n_api_key'] }}"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>
            </div>

            {{-- Email --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Email</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                        <input type="email" name="settings[contact_email]" value="{{ $settings['email']['contact_email'] }}"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
                        <input type="email" name="settings[support_email]" value="{{ $settings['email']['support_email'] }}"
                               class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>
            </div>

        </div>

        <div class="mt-6">
            <button type="submit" class="px-6 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                Save Settings
            </button>
        </div>
    </form>

</x-admin-layout>

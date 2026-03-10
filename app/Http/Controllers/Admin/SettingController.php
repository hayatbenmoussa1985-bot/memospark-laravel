<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Settings page.
     */
    public function index()
    {
        $settings = [
            'general' => [
                'app_name' => AppSetting::getValue('app_name', 'MemoSpark'),
                'app_description' => AppSetting::getValue('app_description', 'Educational flashcard app'),
                'maintenance_mode' => AppSetting::getValue('maintenance_mode', false),
                'registration_enabled' => AppSetting::getValue('registration_enabled', true),
            ],
            'ai' => [
                'openrouter_api_key' => AppSetting::getValue('openrouter_api_key', ''),
                'openrouter_model' => AppSetting::getValue('openrouter_model', 'openai/gpt-4o-mini'),
                'fal_ai_api_key' => AppSetting::getValue('fal_ai_api_key', ''),
                'ai_generation_enabled' => AppSetting::getValue('ai_generation_enabled', true),
            ],
            'n8n' => [
                'n8n_webhook_url' => AppSetting::getValue('n8n_webhook_url', ''),
                'n8n_api_key' => AppSetting::getValue('n8n_api_key', ''),
            ],
            'email' => [
                'contact_email' => AppSetting::getValue('contact_email', 'contact@memospark.net'),
                'support_email' => AppSetting::getValue('support_email', 'support@memospark.net'),
            ],
        ];

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable'],
        ]);

        $oldValues = [];
        $newValues = [];

        foreach ($validated['settings'] as $key => $value) {
            $oldValues[$key] = AppSetting::getValue($key);

            // Handle boolean fields
            if (in_array($key, ['maintenance_mode', 'registration_enabled', 'ai_generation_enabled'])) {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }

            AppSetting::setValue($key, $value);
            $newValues[$key] = $value;
        }

        AuditLog::record(
            action: 'settings_updated',
            targetType: 'app_setting',
            targetId: 0,
            oldValues: $oldValues,
            newValues: $newValues,
        );

        return back()->with('success', 'Settings updated successfully.');
    }
}

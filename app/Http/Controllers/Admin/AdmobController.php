<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdmobController extends Controller
{
    /**
     * Default AdMob configuration structure.
     */
    private function getDefaults(): array
    {
        return [
            'enabled' => false,
            'publisher_id' => '',
            'android' => [
                'app_id' => '',
                'banner_id' => '',
                'interstitial_id' => '',
                'rewarded_id' => '',
                'native_id' => '',
            ],
            'ios' => [
                'app_id' => '',
                'banner_id' => '',
                'interstitial_id' => '',
                'rewarded_id' => '',
                'native_id' => '',
            ],
            'frequency' => [
                'interstitial_every_n_sessions' => 3,
                'min_time_between_ads_seconds' => 180,
                'native_every_n_items' => 5,
            ],
            'compliance' => [
                'coppa_compliant' => true,
                'gdpr_compliant' => true,
                'max_ad_content_rating' => 'G',
            ],
        ];
    }

    /**
     * Show AdMob configuration page.
     */
    public function index()
    {
        $config = AppSetting::getValue('admob_config', $this->getDefaults());

        // Ensure all keys exist (merge with defaults)
        $config = array_replace_recursive($this->getDefaults(), $config);

        return view('admin.admob.index', compact('config'));
    }

    /**
     * Update AdMob configuration.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'enabled' => ['nullable'],
            'publisher_id' => ['nullable', 'string', 'max:50'],

            // Android
            'android_app_id' => ['nullable', 'string', 'max:255'],
            'android_banner_id' => ['nullable', 'string', 'max:255'],
            'android_interstitial_id' => ['nullable', 'string', 'max:255'],
            'android_rewarded_id' => ['nullable', 'string', 'max:255'],
            'android_native_id' => ['nullable', 'string', 'max:255'],

            // iOS
            'ios_app_id' => ['nullable', 'string', 'max:255'],
            'ios_banner_id' => ['nullable', 'string', 'max:255'],
            'ios_interstitial_id' => ['nullable', 'string', 'max:255'],
            'ios_rewarded_id' => ['nullable', 'string', 'max:255'],
            'ios_native_id' => ['nullable', 'string', 'max:255'],

            // Frequency
            'interstitial_every_n_sessions' => ['nullable', 'integer', 'min:1', 'max:100'],
            'min_time_between_ads_seconds' => ['nullable', 'integer', 'min:10', 'max:3600'],
            'native_every_n_items' => ['nullable', 'integer', 'min:1', 'max:100'],

            // Compliance
            'coppa_compliant' => ['nullable'],
            'gdpr_compliant' => ['nullable'],
            'max_ad_content_rating' => ['nullable', 'string', 'in:G,PG,T,MA'],
        ]);

        $oldConfig = AppSetting::getValue('admob_config', $this->getDefaults());

        $newConfig = [
            'enabled' => $request->boolean('enabled'),
            'publisher_id' => $validated['publisher_id'] ?? '',
            'android' => [
                'app_id' => $validated['android_app_id'] ?? '',
                'banner_id' => $validated['android_banner_id'] ?? '',
                'interstitial_id' => $validated['android_interstitial_id'] ?? '',
                'rewarded_id' => $validated['android_rewarded_id'] ?? '',
                'native_id' => $validated['android_native_id'] ?? '',
            ],
            'ios' => [
                'app_id' => $validated['ios_app_id'] ?? '',
                'banner_id' => $validated['ios_banner_id'] ?? '',
                'interstitial_id' => $validated['ios_interstitial_id'] ?? '',
                'rewarded_id' => $validated['ios_rewarded_id'] ?? '',
                'native_id' => $validated['ios_native_id'] ?? '',
            ],
            'frequency' => [
                'interstitial_every_n_sessions' => (int) ($validated['interstitial_every_n_sessions'] ?? 3),
                'min_time_between_ads_seconds' => (int) ($validated['min_time_between_ads_seconds'] ?? 180),
                'native_every_n_items' => (int) ($validated['native_every_n_items'] ?? 5),
            ],
            'compliance' => [
                'coppa_compliant' => $request->boolean('coppa_compliant'),
                'gdpr_compliant' => $request->boolean('gdpr_compliant'),
                'max_ad_content_rating' => $validated['max_ad_content_rating'] ?? 'G',
            ],
        ];

        AppSetting::setValue('admob_config', $newConfig, 'AdMob advertising configuration');

        // Clear the API cache too
        Cache::forget('admob_api_config');

        AuditLog::record(
            action: 'admob_config_updated',
            targetType: 'app_setting',
            targetId: 0,
            oldValues: $oldConfig,
            newValues: $newConfig,
        );

        return back()->with('success', 'AdMob configuration updated successfully.');
    }
}

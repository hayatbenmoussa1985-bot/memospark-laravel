<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdConfigController extends Controller
{
    /**
     * Google Test Ad Unit IDs (used as fallback in development).
     */
    private const TEST_ADS = [
        'android' => [
            'app_id' => 'ca-app-pub-3940256099942544~3347511713',
            'banner_id' => 'ca-app-pub-3940256099942544/6300978111',
            'interstitial_id' => 'ca-app-pub-3940256099942544/1033173712',
            'rewarded_id' => 'ca-app-pub-3940256099942544/5224354917',
            'native_id' => 'ca-app-pub-3940256099942544/2247696110',
        ],
        'ios' => [
            'app_id' => 'ca-app-pub-3940256099942544~1458002511',
            'banner_id' => 'ca-app-pub-3940256099942544/2934735716',
            'interstitial_id' => 'ca-app-pub-3940256099942544/4411468910',
            'rewarded_id' => 'ca-app-pub-3940256099942544/1712485313',
            'native_id' => 'ca-app-pub-3940256099942544/3986624511',
        ],
    ];

    /**
     * Get ad configuration for the mobile app.
     *
     * Public endpoint — no authentication required.
     * Platform is detected via X-Platform header (ios/android).
     * Response is cached for 30 seconds.
     */
    public function getConfig(Request $request): JsonResponse
    {
        $platform = strtolower($request->header('X-Platform', 'android'));
        if (!in_array($platform, ['ios', 'android'])) {
            $platform = 'android';
        }

        $cacheKey = "admob_api_config:{$platform}";

        $config = Cache::remember($cacheKey, 30, function () use ($platform) {
            return $this->buildConfig($platform);
        });

        return response()->json($config)
            ->header('Cache-Control', 'public, max-age=30, must-revalidate');
    }

    /**
     * Build the ad config for a specific platform.
     */
    private function buildConfig(string $platform): array
    {
        $dbConfig = AppSetting::getValue('admob_config');

        if (!$dbConfig) {
            // Fallback to test ads if no config in database
            return $this->getFallbackConfig($platform);
        }

        $platformConfig = $dbConfig[$platform] ?? [];

        return [
            'enabled' => (bool) ($dbConfig['enabled'] ?? false),
            'publisherId' => $dbConfig['publisher_id'] ?? '',
            'appId' => $platformConfig['app_id'] ?? '',
            'bannerId' => $platformConfig['banner_id'] ?? '',
            'interstitialId' => $platformConfig['interstitial_id'] ?? '',
            'rewardedId' => $platformConfig['rewarded_id'] ?? '',
            'nativeId' => $platformConfig['native_id'] ?? '',
            'frequency' => [
                'interstitial_every_n_sessions' => (int) ($dbConfig['frequency']['interstitial_every_n_sessions'] ?? 3),
                'min_time_between_ads_seconds' => (int) ($dbConfig['frequency']['min_time_between_ads_seconds'] ?? 180),
                'native_every_n_items' => (int) ($dbConfig['frequency']['native_every_n_items'] ?? 5),
            ],
            'compliance' => [
                'coppa_compliant' => (bool) ($dbConfig['compliance']['coppa_compliant'] ?? true),
                'gdpr_compliant' => (bool) ($dbConfig['compliance']['gdpr_compliant'] ?? true),
                'max_ad_content_rating' => $dbConfig['compliance']['max_ad_content_rating'] ?? 'G',
            ],
        ];
    }

    /**
     * Fallback config using Google Test Ad Unit IDs.
     */
    private function getFallbackConfig(string $platform): array
    {
        $test = self::TEST_ADS[$platform];

        return [
            'enabled' => false,
            'publisherId' => '',
            'appId' => $test['app_id'],
            'bannerId' => $test['banner_id'],
            'interstitialId' => $test['interstitial_id'],
            'rewardedId' => $test['rewarded_id'],
            'nativeId' => $test['native_id'],
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
     * Clear the ad config cache (admin action).
     */
    public function clearCache(): JsonResponse
    {
        Cache::forget('admob_api_config:ios');
        Cache::forget('admob_api_config:android');
        Cache::forget('app_setting:admob_config');

        return response()->json(['message' => 'Ad config cache cleared.']);
    }
}

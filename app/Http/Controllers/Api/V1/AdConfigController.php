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
     * Build the ad config (returns both platforms in nested format for mobile app).
     */
    private function buildConfig(string $platform): array
    {
        $dbConfig = AppSetting::getValue('admob_config');

        if (!$dbConfig) {
            // Fallback to test ads if no config in database
            return $this->getFallbackConfig($platform);
        }

        $androidConfig = $dbConfig['android'] ?? [];
        $iosConfig = $dbConfig['ios'] ?? [];

        return [
            'enabled' => (bool) ($dbConfig['enabled'] ?? false),
            'publisherId' => $dbConfig['publisher_id'] ?? '',
            'android' => [
                'appId' => $androidConfig['app_id'] ?? '',
                'bannerId' => $androidConfig['banner_id'] ?? '',
                'interstitialId' => $androidConfig['interstitial_id'] ?? '',
                'rewardedId' => $androidConfig['rewarded_id'] ?? '',
                'nativeId' => $androidConfig['native_id'] ?? '',
            ],
            'ios' => [
                'appId' => $iosConfig['app_id'] ?? '',
                'bannerId' => $iosConfig['banner_id'] ?? '',
                'interstitialId' => $iosConfig['interstitial_id'] ?? '',
                'rewardedId' => $iosConfig['rewarded_id'] ?? '',
                'nativeId' => $iosConfig['native_id'] ?? '',
            ],
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
     * Fallback config using Google Test Ad Unit IDs (both platforms).
     */
    private function getFallbackConfig(string $platform): array
    {
        return [
            'enabled' => false,
            'publisherId' => '',
            'android' => [
                'appId' => self::TEST_ADS['android']['app_id'],
                'bannerId' => self::TEST_ADS['android']['banner_id'],
                'interstitialId' => self::TEST_ADS['android']['interstitial_id'],
                'rewardedId' => self::TEST_ADS['android']['rewarded_id'],
                'nativeId' => self::TEST_ADS['android']['native_id'],
            ],
            'ios' => [
                'appId' => self::TEST_ADS['ios']['app_id'],
                'bannerId' => self::TEST_ADS['ios']['banner_id'],
                'interstitialId' => self::TEST_ADS['ios']['interstitial_id'],
                'rewardedId' => self::TEST_ADS['ios']['rewarded_id'],
                'nativeId' => self::TEST_ADS['ios']['native_id'],
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

<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'free',
                'name' => 'Free',
                'price' => 0,
                'currency' => 'USD',
                'duration_days' => 0,
                'features' => ['max_decks' => 5, 'max_cards_per_deck' => 20, 'ai_generation' => false],
                'sort_order' => 0,
            ],
            [
                'slug' => 'weekly',
                'name' => 'Weekly',
                'price' => 1.99,
                'currency' => 'USD',
                'duration_days' => 7,
                'apple_product_id' => 'com.memospark.weekly',
                'features' => ['max_decks' => -1, 'max_cards_per_deck' => -1, 'ai_generation' => true],
                'sort_order' => 1,
            ],
            [
                'slug' => 'monthly',
                'name' => 'Monthly',
                'price' => 4.99,
                'currency' => 'USD',
                'duration_days' => 30,
                'apple_product_id' => 'com.memospark.monthly',
                'features' => ['max_decks' => -1, 'max_cards_per_deck' => -1, 'ai_generation' => true],
                'sort_order' => 2,
            ],
            [
                'slug' => 'yearly',
                'name' => 'Yearly',
                'price' => 29.99,
                'currency' => 'USD',
                'duration_days' => 365,
                'apple_product_id' => 'com.memospark.yearly',
                'features' => ['max_decks' => -1, 'max_cards_per_deck' => -1, 'ai_generation' => true],
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}

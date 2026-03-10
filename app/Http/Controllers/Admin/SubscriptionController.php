<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Subscription analytics overview.
     */
    public function index(Request $request)
    {
        // Plans overview
        $plans = SubscriptionPlan::withCount([
            'subscriptions',
            'subscriptions as active_subscriptions_count' => function ($q) {
                $q->where('status', 'active')->where('current_period_end', '>', now());
            },
        ])->ordered()->get();

        // Active subscriptions count
        $totalActive = Subscription::where('status', 'active')
            ->where('current_period_end', '>', now())
            ->count();

        // Monthly revenue estimate
        $monthlyRevenue = Subscription::where('status', 'active')
            ->where('current_period_end', '>', now())
            ->with('plan')
            ->get()
            ->sum(fn ($sub) => $sub->plan ? ($sub->plan->price / max($sub->plan->duration_days, 1)) * 30 : 0);

        // Recent subscriptions
        $recentSubscriptions = Subscription::with(['user', 'plan'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Filter by plan
        if ($planId = $request->input('plan_id')) {
            $recentSubscriptions = Subscription::with(['user', 'plan'])
                ->where('plan_id', $planId)
                ->latest()
                ->paginate(20)
                ->withQueryString();
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $recentSubscriptions = Subscription::with(['user', 'plan'])
                ->where('status', $status)
                ->latest()
                ->paginate(20)
                ->withQueryString();
        }

        return view('admin.subscriptions.index', compact(
            'plans',
            'totalActive',
            'monthlyRevenue',
            'recentSubscriptions',
        ));
    }

    /**
     * Edit subscription plan.
     */
    public function editPlan(SubscriptionPlan $plan)
    {
        return view('admin.subscriptions.edit-plan', compact('plan'));
    }

    /**
     * Update subscription plan.
     */
    public function updatePlan(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:0'],
            'apple_product_id' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        $oldValues = $plan->only(['name', 'price', 'duration_days', 'is_active']);

        $plan->update($validated);

        AuditLog::record(
            action: 'plan_updated',
            targetType: 'subscription_plan',
            targetId: $plan->id,
            oldValues: $oldValues,
            newValues: $validated,
        );

        return redirect()
            ->route('admin.subscriptions.index')
            ->with('success', "Plan \"{$plan->name}\" updated.");
    }
}

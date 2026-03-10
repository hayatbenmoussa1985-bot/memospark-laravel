<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user has an active subscription.
 * Used for premium feature gates.
 * Super admins and admins bypass this check.
 */
class EnsureActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            abort(401);
        }

        // Admins bypass subscription check
        if ($user->isAdmin()) {
            return $next($request);
        }

        if (!$user->hasActiveSubscription()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Premium subscription required.',
                    'upgrade_required' => true,
                ], 403);
            }
            abort(403, 'Premium subscription required.');
        }

        return $next($request);
    }
}

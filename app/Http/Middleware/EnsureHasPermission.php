<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated admin has a specific permission.
 * Super admins bypass all permission checks.
 *
 * Usage: middleware('permission:manage_users')
 */
class EnsureHasPermission
{
    public function handle(Request $request, Closure $next, string $permissionSlug): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            abort(401);
        }

        // Super admin bypasses all permission checks
        if ($user->role === UserRole::SuperAdmin) {
            return $next($request);
        }

        // Check if user has the specific permission
        if (!$user->hasPermission($permissionSlug)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Forbidden. Permission '{$permissionSlug}' required.",
                ], 403);
            }
            abort(403, "Forbidden. Permission '{$permissionSlug}' required.");
        }

        return $next($request);
    }
}

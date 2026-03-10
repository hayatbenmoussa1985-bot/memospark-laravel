<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user is super_admin or admin.
 * Used for admin dashboard routes.
 */
class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden. Admin access required.'], 403);
            }
            abort(403, 'Forbidden. Admin access required.');
        }

        return $next($request);
    }
}

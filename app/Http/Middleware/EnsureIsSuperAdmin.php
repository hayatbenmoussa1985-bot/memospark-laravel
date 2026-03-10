<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user is a super_admin.
 * Only super_admin (Hammadi) can access certain routes.
 */
class EnsureIsSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || $request->user()->role !== UserRole::SuperAdmin) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden. Super admin access required.'], 403);
            }
            abort(403, 'Forbidden. Super admin access required.');
        }

        return $next($request);
    }
}

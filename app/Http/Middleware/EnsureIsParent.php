<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user is a parent or super_admin.
 * Used for parent portal routes.
 */
class EnsureIsParent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        if ($user->role !== UserRole::Parent && $user->role !== UserRole::SuperAdmin) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden. Parent access required.'], 403);
            }
            abort(403, 'Forbidden. Parent access required.');
        }

        return $next($request);
    }
}

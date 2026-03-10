<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Wraps all successful API JSON responses in a { "data": ... } envelope.
 *
 * The mobile app (React Native) expects responses in { data: T } format
 * via ApiResponse<T> TypeScript type. This middleware ensures consistency.
 *
 * - 2xx JsonResponse WITHOUT a top-level "data" key → wrapped automatically
 * - 2xx JsonResponse WITH a top-level "data" key → left as-is (already wrapped)
 * - Non-2xx responses → left as-is (error format: { message, errors })
 */
class WrapApiResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!($response instanceof JsonResponse) || !$response->isSuccessful()) {
            return $response;
        }

        $data = $response->getData(true);

        // Already wrapped (controller used 'data' key explicitly)
        if (is_array($data) && array_key_exists('data', $data)) {
            return $response;
        }

        $response->setData(['data' => $data]);

        return $response;
    }
}

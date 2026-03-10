<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Admin routes: app.memospark.net/admin/* (production)
            // or /admin/* (local dev)
            \Illuminate\Support\Facades\Route::middleware(['web', 'auth', 'admin'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            // User space routes: app.memospark.net/* (production)
            // or /user/* (local dev)
            \Illuminate\Support\Facades\Route::middleware(['web', 'auth'])
                ->prefix('user')
                ->name('user.')
                ->group(base_path('routes/user.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware aliases for route-level usage
        $middleware->alias([
            'super_admin' => \App\Http\Middleware\EnsureIsSuperAdmin::class,
            'admin' => \App\Http\Middleware\EnsureIsAdmin::class,
            'parent' => \App\Http\Middleware\EnsureIsParent::class,
            'permission' => \App\Http\Middleware\EnsureHasPermission::class,
            'subscribed' => \App\Http\Middleware\EnsureActiveSubscription::class,
            'force_json' => \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        // Apply ForceJsonResponse to all API routes
        $middleware->prependToGroup('api', [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        // Sanctum stateful domains for SPA (if needed later)
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

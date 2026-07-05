<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);

        // Note: the payment/* POST callbacks below also fully exclude the
        // PreventRequestForgery middleware in routes/web.php (via
        // Route::withoutMiddleware), since SSLCommerz's cross-site POST has
        // no session to validate a token against. This `except` entry is
        // kept as a documented fallback/defense-in-depth.
        $middleware->validateCsrfTokens(except: [
            'payment/ipn',
            'payment/success',
            'payment/fail',
            'payment/cancel',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();

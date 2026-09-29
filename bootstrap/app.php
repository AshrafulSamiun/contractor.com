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
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $trustedProxies = array_values(array_filter(array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', '')))));
        if ($trustedProxies) $middleware->trustProxies(at: $trustedProxies);
        $middleware->alias([
            'plan.feature' => \App\Http\Middleware\EnsurePlanFeature::class,
            'permission' => \App\Http\Middleware\EnsurePermission::class,
            'security.activity' => \App\Http\Middleware\TrackAccountSecurityActivity::class,
            'platform.admin' => \App\Http\Middleware\EnsurePlatformAdmin::class,
            'customer.panel' => \App\Http\Middleware\EnsureCustomerPanelAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

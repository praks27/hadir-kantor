<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureApprover;
use App\Http\Middleware\EnsureEmployeeActions;
use App\Http\Middleware\EnsureMenuAccess;
use App\Http\Middleware\EnsureSuperAdmin;
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
    // Trust local reverse proxy (Nginx / Herd)
    $middleware->trustProxies(
        at: ['127.0.0.1', '::1'],
        headers: Request::HEADER_X_FORWARDED_FOR |
                 Request::HEADER_X_FORWARDED_HOST |
                 Request::HEADER_X_FORWARDED_PORT |
                 Request::HEADER_X_FORWARDED_PROTO
    );

    $middleware->alias([
        'superadmin' => EnsureSuperAdmin::class,
        'admin' => EnsureAdmin::class,
        'employee-actions' => EnsureEmployeeActions::class,
        'approver' => EnsureApprover::class,
        'menu-access' => EnsureMenuAccess::class,
    ]);
})

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

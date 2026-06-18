<?php

use App\Exceptions\PlanFeatureException;
use App\Exceptions\QuotaExceededException;
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
    ->withProviders([
        \App\Providers\HorizonServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\EnsureUserNotBanned::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (QuotaExceededException $e, Request $request) {
            return response()->json([
                'message'      => 'Đã đạt giới hạn khách mời. Nâng gói để tiếp tục.',
                'error'        => 'quota_exceeded',
                'resource'     => $e->resource,
                'available'    => $e->available,
                'current_plan' => $e->currentPlan,
            ], 422);
        });

        $exceptions->render(function (PlanFeatureException $e, Request $request) {
            $labels = [
                'email'  => 'Gửi email thiệp',
                'export' => 'Export Excel',
                'table'  => 'Quản lý bàn tiệc',
            ];
            return response()->json([
                'message'      => ($labels[$e->feature] ?? $e->feature) . " không có trong gói {$e->currentPlan}.",
                'error'        => 'feature_not_available',
                'feature'      => $e->feature,
                'current_plan' => $e->currentPlan,
            ], 422);
        });
    })->create();

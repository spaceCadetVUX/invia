<?php

namespace App\Http\Middleware;

use App\Services\PlanService;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EnsureEventNotExpired
{
    private array $except = [
        'dashboard.events.upgrade',
        'dashboard.events.payment.create',
        'dashboard.events.payment.return',
        'dashboard.events.payment.cancel',
        'dashboard.events.payment.coupon-preview',
    ];

    public function handle(Request $request, Closure $next): mixed
    {
        $event = $request->route('event');

        if ($event && app(PlanService::class)->isExpired($event)) {
            $routeName = $request->route()->getName();

            if (in_array($routeName, $this->except)) {
                return $next($request);
            }

            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json(['message' => 'Sự kiện đã hết hạn lưu trữ.'], 403);
            }

            return Inertia::render('Dashboard/Events/Expired', [
                'event' => $event->only('id', 'slug', 'title', 'expires_at'),
            ])->toResponse($request)->setStatusCode(403);
        }

        return $next($request);
    }
}

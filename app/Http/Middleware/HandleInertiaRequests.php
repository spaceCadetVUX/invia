<?php

namespace App\Http\Middleware;

use App\Models\SystemAnnouncement;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth'         => [
                'user' => $request->user(),
            ],
            'announcement' => fn () => SystemAnnouncement::where('is_active', true)
                ->where(fn ($q) =>
                    $q->whereNull('starts_at')->orWhere('starts_at', '<=', now())
                )
                ->where(fn ($q) =>
                    $q->whereNull('ends_at')->orWhere('ends_at', '>=', now())
                )
                ->latest()
                ->first(['id', 'title', 'body', 'type']),
        ];
    }
}

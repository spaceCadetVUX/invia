<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWishRequest;
use App\Models\Event;
use App\Services\WishService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WishController extends Controller
{
    public function __construct(private WishService $wishService) {}

    public function store(StoreWishRequest $request, string $slug): JsonResponse|RedirectResponse
    {
        $event = Event::where('slug', $slug)->where('status', 'published')->firstOrFail();

        abort_if(!$event->wishes_enabled, 403);

        $wish = $this->wishService->submit(
            event: $event,
            data:  $request->validated(),
            token: $request->query('t'),
            ip:    $request->ip(),
        );

        if ($request->expectsJson()) {
            return response()->json([
                'id'         => $wish->id,
                'name'       => $wish->guest->name ?? $request->validated('name'),
                'message'    => $wish->message,
                'is_pinned'  => false,
                'created_at' => $wish->created_at->diffForHumans(),
            ], 201);
        }

        return back()->with('wish_sent', true);
    }

    public function index(string $slug, Request $request): JsonResponse
    {
        $event = Event::where('slug', $slug)->where('status', 'published')->firstOrFail();

        $wishes = $this->wishService->getPublicWishes($event, $request->query('after'));

        return response()->json($wishes);
    }
}

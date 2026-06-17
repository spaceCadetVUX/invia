<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Wish;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class DashboardWishController extends Controller
{
    public function index(Event $event): Response
    {
        $this->authorize('update', $event);

        return Inertia::render('Dashboard/Events/Wishes', [
            'event'  => $event->only('id', 'slug', 'title'),
            'wishes' => Wish::where('event_id', $event->id)
                ->with('guest:id,name,email')
                ->orderByDesc('is_pinned')
                ->orderByDesc('created_at')
                ->paginate(30),
        ]);
    }

    public function pin(Event $event, Wish $wish): JsonResponse
    {
        $this->authorize('update', $event);
        abort_if($wish->event_id !== $event->id, 403);

        $wish->update(['is_pinned' => !$wish->is_pinned]);

        return response()->json(['is_pinned' => $wish->is_pinned]);
    }

    public function hide(Event $event, Wish $wish): JsonResponse
    {
        $this->authorize('update', $event);
        abort_if($wish->event_id !== $event->id, 403);

        $wish->update(['is_hidden' => !$wish->is_hidden]);

        return response()->json(['is_hidden' => $wish->is_hidden]);
    }

    public function destroy(Event $event, Wish $wish): JsonResponse
    {
        $this->authorize('update', $event);
        abort_if($wish->event_id !== $event->id, 403);

        $wish->delete();

        return response()->json(['deleted' => true]);
    }
}

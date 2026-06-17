<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendInvitationRequest;
use App\Models\Event;
use App\Services\SendInvitationService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class SendInvitationController extends Controller
{
    public function __construct(private SendInvitationService $sendService) {}

    public function index(Event $event): Response
    {
        $this->authorize('update', $event);

        return Inertia::render('Dashboard/Events/Send', [
            'event' => $event->only('id', 'slug', 'title', 'status'),
            'stats' => $this->sendService->getSendStats($event),
        ]);
    }

    public function send(SendInvitationRequest $request, Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        abort_if($event->status !== 'published', 422, __('send.event_not_published'));

        $count = $this->sendService->dispatch(
            event:    $event,
            mode:     $request->validated('mode'),
            guestIds: $request->validated('guest_ids'),
        );

        return response()->json([
            'queued'  => $count,
            'message' => __('send.queued', ['count' => $count]),
        ]);
    }

    public function progress(Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        return response()->json($this->sendService->getSendStats($event));
    }
}

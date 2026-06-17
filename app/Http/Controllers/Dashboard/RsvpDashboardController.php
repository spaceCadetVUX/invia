<?php

namespace App\Http\Controllers\Dashboard;

use App\Exports\RsvpExport;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Rsvp;
use App\Services\RsvpDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RsvpDashboardController extends Controller
{
    public function __construct(private RsvpDashboardService $service) {}

    public function index(Event $event, Request $request): Response
    {
        $this->authorize('update', $event);

        $filters = $request->only(['status', 'search', 'sort', 'direction']);

        return Inertia::render('Dashboard/Events/RsvpDashboard', [
            'event'   => $event->only('id', 'slug', 'title', 'event_date'),
            'rsvps'   => $this->service->getFiltered($event, $filters),
            'summary' => $this->service->getSummary($event),
            'filters' => $filters,
        ]);
    }

    public function export(Event $event): StreamedResponse
    {
        $this->authorize('update', $event);

        return (new RsvpExport($event))->download("rsvp-{$event->slug}.xlsx");
    }

    public function assignTable(Request $request, Event $event, Rsvp $rsvp): JsonResponse
    {
        $this->authorize('update', $event);
        abort_if($rsvp->event_id !== $event->id, 403);

        $data = $request->validate([
            'table_no' => ['nullable', 'string', 'max:20'],
        ]);

        $rsvp->guest->update(['table_no' => $data['table_no']]);

        return response()->json(['table_no' => $data['table_no']]);
    }
}

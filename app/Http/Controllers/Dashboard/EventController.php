<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\EventType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Services\EventService;
use App\Services\TemplateService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function __construct(
        private EventService    $eventService,
        private TemplateService $templateService,
    ) {}

    public function create(): Response
    {
        return Inertia::render('Dashboard/Events/Create', [
            'templates'  => $this->templateService->listForUser(auth()->user()),
            'eventTypes' => collect(EventType::cases())->map(fn($t) => [
                'value' => $t->value,
                'label' => $t->label(),
            ]),
        ]);
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $event = $this->eventService->createEvent(
            auth()->user(),
            $request->validated()
        );

        return redirect()
            ->route('dashboard.events.show', $event->slug)
            ->with('success', __('event.created'));
    }

    public function show(string $slug): Response
    {
        $event = auth()->user()->events()
            ->where('slug', $slug)
            ->with('template')
            ->firstOrFail();

        return Inertia::render('Dashboard/Events/Show', [
            'event' => $event,
        ]);
    }
}

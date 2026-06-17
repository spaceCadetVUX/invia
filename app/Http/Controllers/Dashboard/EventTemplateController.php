<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEventTemplateRequest;
use App\Models\Event;
use App\Services\TemplateService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EventTemplateController extends Controller
{
    public function __construct(private TemplateService $templateService) {}

    public function edit(Event $event): Response
    {
        $this->authorize('update', $event);

        return Inertia::render('Dashboard/Events/TemplateSelect', [
            'event'           => $event->only('id', 'slug', 'title', 'template_id'),
            'templates'       => $this->templateService->listForUser(auth()->user()),
            'currentTemplate' => $event->template_id,
        ]);
    }

    public function update(UpdateEventTemplateRequest $request, Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        $this->templateService->changeTemplate(
            auth()->user(),
            $event,
            $request->validated('template_id')
        );

        return redirect()
            ->route('dashboard.events.show', $event->slug)
            ->with('success', __('event.template_changed'));
    }
}

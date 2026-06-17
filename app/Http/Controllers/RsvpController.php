<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRsvpRequest;
use App\Models\Event;
use App\Services\RsvpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RsvpController extends Controller
{
    public function __construct(private RsvpService $rsvpService) {}

    public function form(string $slug, Request $request): View
    {
        $event = $this->rsvpService->resolvePublishedEvent($slug);
        $guest = $this->rsvpService->resolveGuest($request->query('t'), $event);

        abort_if(!$event->rsvp_enabled, 404);

        $existingRsvp = $guest
            ? $guest->rsvp()->where('event_id', $event->id)->first()
            : null;

        return view('invitation.rsvp', compact('event', 'guest', 'existingRsvp'));
    }

    public function store(StoreRsvpRequest $request, string $slug): RedirectResponse
    {
        $event = $this->rsvpService->resolvePublishedEvent($slug);

        abort_if(!$event->rsvp_enabled, 403);

        $rsvp = $this->rsvpService->handleRsvp(
            event: $event,
            data:  $request->validated(),
            token: $request->query('t'),
            ip:    $request->ip(),
        );

        return redirect()
            ->route('invitation.rsvp.success', $slug)
            ->with('rsvp_status', $rsvp->status);
    }

    public function success(string $slug, Request $request): View
    {
        $event = Event::where('slug', $slug)->where('status', 'published')->firstOrFail();

        return view('invitation.rsvp-success', [
            'event'       => $event,
            'rsvp_status' => session('rsvp_status', 'yes'),
        ]);
    }
}

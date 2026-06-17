<?php

namespace App\Http\Controllers;

use App\Jobs\TrackEventView;
use App\Services\InvitationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function __construct(private InvitationService $invitationService) {}

    public function show(string $slug, Request $request): View|Response
    {
        $event = Cache::remember("invitation:{$slug}", 300, fn () =>
            $this->invitationService->resolveEvent($slug)
        );

        $guest = $this->invitationService->resolveGuest($request->query('t'), $event);

        TrackEventView::dispatchAfterResponse($event->id, $guest?->id);

        return view('templates.' . $event->template->blade_file . '.index', [
            'event'  => $event,
            'guest'  => $guest,
            'music'  => $this->invitationService->resolveMusic($event),
            'ogMeta' => $this->invitationService->buildOgMeta($event),
        ]);
    }

    public function calendar(string $slug): Response
    {
        $event = $this->invitationService->resolveEvent($slug);

        $ics = $this->invitationService->generateIcs($event);

        return response($ics, 200, [
            'Content-Type'        => 'text/calendar; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="su-kien.ics"',
        ]);
    }
}

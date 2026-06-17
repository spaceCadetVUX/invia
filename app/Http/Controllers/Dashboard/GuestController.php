<?php

namespace App\Http\Controllers\Dashboard;

use App\Exports\GuestsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportGuestRequest;
use App\Http\Requests\StoreGuestRequest;
use App\Http\Requests\UpdateGuestRequest;
use App\Models\Event;
use App\Models\Guest;
use App\Services\GuestService;
use App\Services\PlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GuestController extends Controller
{
    public function __construct(
        private GuestService $guestService,
        private PlanService  $planService,
    ) {}

    public function index(Event $event): Response
    {
        $this->authorize('update', $event);

        return Inertia::render('Dashboard/Events/Guests', [
            'event'           => $event->only('id', 'slug', 'title', 'plan'),
            'guests'          => Guest::where('event_id', $event->id)
                ->with('rsvp:guest_id,status,plus_one')
                ->orderBy('name')
                ->paginate(50),
            'stats'           => $this->guestService->getStats($event),
            'quota'           => $this->planService->getQuota($event),
            'selfRegisterUrl' => route('invitation.register.form', $event->slug),
        ]);
    }

    public function store(StoreGuestRequest $request, Event $event): JsonResponse
    {
        $this->authorize('update', $event);
        $this->planService->assertCanAddGuests($event);

        $guest = $this->guestService->addGuest($event, $request->validated());

        return response()->json($guest->only('id', 'name', 'email', 'phone', 'table_no', 'token'), 201);
    }

    public function update(UpdateGuestRequest $request, Event $event, Guest $guest): JsonResponse
    {
        $this->authorize('update', $event);
        abort_if($guest->event_id !== $event->id, 403);

        $guest->update($request->validated());

        return response()->json($guest->fresh()->only('id', 'name', 'email', 'phone', 'table_no'));
    }

    public function destroy(Event $event, Guest $guest): JsonResponse
    {
        $this->authorize('update', $event);
        abort_if($guest->event_id !== $event->id, 403);

        $guest->delete();

        return response()->json(['deleted' => true]);
    }

    public function import(ImportGuestRequest $request, Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        // Đếm số dòng hợp lệ trước khi import để check quota bulk
        $rowCount = $this->guestService->countImportRows($request->file('file'));
        $this->planService->assertCanAddGuests($event, max(1, $rowCount));

        $result = $this->guestService->importExcel($event, $request->file('file'));

        return response()->json($result);
    }

    public function export(Event $event): StreamedResponse
    {
        $this->authorize('update', $event);

        return (new GuestsExport($event))->download("khach-moi-{$event->slug}.xlsx");
    }
}

<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Wish;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class DashboardWishController extends Controller
{
    public function index(Event $event, Request $request): Response
    {
        $this->authorize('update', $event);

        $filters = $request->only(['search']);

        return Inertia::render('Dashboard/Events/WishesDashboard', [
            'event'   => $event->only('id', 'slug', 'title'),
            'wishes'  => $this->buildQuery($event, $filters)->paginate(30)->withQueryString(),
            'summary' => $this->getSummary($event),
            'filters' => $filters,
        ]);
    }

    public function exportPdf(Event $event): HttpResponse
    {
        $this->authorize('update', $event);

        $wishes = Wish::where('event_id', $event->id)
            ->where('is_hidden', false)
            ->with('guest:id,name')
            ->orderByDesc('is_pinned')
            ->orderBy('created_at')
            ->get();

        $pdf = Pdf::loadView('pdf.wishes-book', [
            'event'  => $event,
            'wishes' => $wishes,
        ]);

        return $pdf->download("so-loi-chuc-{$event->slug}.pdf");
    }

    private function buildQuery(Event $event, array $filters = []): Builder
    {
        $query = Wish::where('event_id', $event->id)
            ->with('guest:id,name,email')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at');

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('message', 'ilike', '%' . $filters['search'] . '%')
                  ->orWhereHas('guest', fn ($g) =>
                      $g->where('name', 'ilike', '%' . $filters['search'] . '%')
                  );
            });
        }

        return $query;
    }

    private function getSummary(Event $event): array
    {
        return [
            'total'   => Wish::where('event_id', $event->id)->count(),
            'pinned'  => Wish::where('event_id', $event->id)->where('is_pinned', true)->count(),
            'hidden'  => Wish::where('event_id', $event->id)->where('is_hidden', true)->count(),
            'visible' => Wish::where('event_id', $event->id)->where('is_hidden', false)->count(),
        ];
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

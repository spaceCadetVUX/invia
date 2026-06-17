<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Rsvp;
use Illuminate\Pagination\LengthAwarePaginator;

class RsvpDashboardService
{
    public function getFiltered(Event $event, array $filters): LengthAwarePaginator
    {
        $query = Rsvp::where('rsvp.event_id', $event->id)
            ->with('guest:id,name,email,phone,table_no')
            ->select('rsvp.*');

        if (!empty($filters['status']) && in_array($filters['status'], ['yes', 'no', 'maybe'])) {
            $query->where('rsvp.status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->whereHas('guest', fn ($q) =>
                $q->where('name', 'ilike', '%' . $filters['search'] . '%')
            );
        }

        $sort = $filters['sort'] ?? 'created_at';
        $dir  = ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        if ($sort === 'name') {
            $query->join('guests', 'rsvp.guest_id', '=', 'guests.id')
                  ->orderBy('guests.name', $dir);
        } else {
            $sortCol = match ($sort) {
                'status'     => 'rsvp.status',
                'plus_one'   => 'rsvp.plus_one',
                default      => 'rsvp.created_at',
            };
            $query->orderBy($sortCol, $dir);
        }

        return $query->paginate(50)->withQueryString();
    }

    public function getSummary(Event $event): array
    {
        $rows = Rsvp::where('event_id', $event->id)
            ->selectRaw('status, COUNT(*) as count, COALESCE(SUM(plus_one), 0) as plus_ones')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $yes   = $rows['yes']   ?? null;
        $no    = $rows['no']    ?? null;
        $maybe = $rows['maybe'] ?? null;

        $totalGuests = ($yes?->count   ?? 0) + ($maybe?->count ?? 0);
        $totalPeople = $totalGuests
                     + ($yes?->plus_ones   ?? 0)
                     + ($maybe?->plus_ones ?? 0);

        return [
            'yes'          => (int) ($yes?->count   ?? 0),
            'no'           => (int) ($no?->count    ?? 0),
            'maybe'        => (int) ($maybe?->count ?? 0),
            'total_guests' => (int) $totalGuests,
            'total_people' => (int) $totalPeople,
        ];
    }
}

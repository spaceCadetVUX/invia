<?php

namespace App\Services;

use App\Jobs\SendInvitationEmail;
use App\Models\Event;
use App\Models\Guest;
use Illuminate\Support\Collection;

class SendInvitationService
{
    public function dispatch(Event $event, string $mode, ?array $guestIds): int
    {
        $guests = $this->resolveGuests($event, $mode, $guestIds);

        $eligible = $guests->filter(fn ($g) =>
            $g->email &&
            !$g->unsubscribed_at
        );

        foreach ($eligible as $guest) {
            SendInvitationEmail::dispatch($event, $guest)->onQueue('emails');
        }

        return $eligible->count();
    }

    public function getSendStats(Event $event): array
    {
        $base         = Guest::where('event_id', $event->id)->whereNotNull('email');
        $total        = $base->count();
        $sent         = (clone $base)->whereNotNull('email_sent_at')->count();
        $unsubscribed = (clone $base)->whereNotNull('unsubscribed_at')->count();
        $unsent       = (clone $base)->whereNull('email_sent_at')->whereNull('unsubscribed_at')->count();

        return compact('total', 'sent', 'unsent', 'unsubscribed');
    }

    private function resolveGuests(Event $event, string $mode, ?array $guestIds): Collection
    {
        $query = Guest::where('event_id', $event->id);

        return match ($mode) {
            'all'    => $query->get(),
            'unsent' => $query->whereNull('email_sent_at')->get(),
            'manual' => $query->whereIn('id', $guestIds ?? [])->get(),
        };
    }
}

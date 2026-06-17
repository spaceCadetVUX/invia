<?php

namespace App\Services;

use App\Jobs\SendRsvpConfirmation;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Rsvp;
use App\Repositories\GuestRepository;
use App\Repositories\RsvpRepository;
use Illuminate\Support\Str;

class RsvpService
{
    public function __construct(
        private RsvpRepository     $rsvpRepo,
        private GuestRepository    $guestRepo,
        private NotificationService $notifService,
    ) {}

    public function handleRsvp(Event $event, array $data, ?string $token, string $ip): Rsvp
    {
        $guest = $token
            ? Guest::where('token', $token)->where('event_id', $event->id)->first()
            : null;

        return $guest
            ? $this->handleTokenRsvp($event, $data, $guest)
            : $this->handleAnonymousRsvp($event, $data, $ip);
    }

    public function resolvePublishedEvent(string $slug): Event
    {
        return Event::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();
    }

    public function resolveGuest(?string $token, Event $event): ?Guest
    {
        if (!$token) return null;
        return Guest::where('token', $token)->where('event_id', $event->id)->first();
    }

    private function handleTokenRsvp(Event $event, array $data, Guest $guest): Rsvp
    {
        $rsvp = $this->rsvpRepo->upsert([
            'guest_id' => $guest->id,
            'event_id' => $event->id,
            'status'   => $data['status'],
            'plus_one' => $data['plus_one'] ?? 0,
            'dietary'  => $data['dietary'] ?? null,
        ]);

        $this->afterRsvp($event, $guest, $rsvp, isNew: $rsvp->wasRecentlyCreated);

        return $rsvp;
    }

    private function handleAnonymousRsvp(Event $event, array $data, string $ip): Rsvp
    {
        $guest = $this->guestRepo->create([
            'event_id' => $event->id,
            'name'     => $data['name'],
            'email'    => $data['email'] ?? null,
            'token'    => Str::random(32),
            'source'   => 'self_register',
        ]);

        $rsvp = $this->rsvpRepo->create([
            'guest_id' => $guest->id,
            'event_id' => $event->id,
            'status'   => $data['status'],
            'plus_one' => $data['plus_one'] ?? 0,
            'dietary'  => $data['dietary'] ?? null,
        ]);

        $this->afterRsvp($event, $guest, $rsvp, isNew: true);

        return $rsvp;
    }

    private function afterRsvp(Event $event, Guest $guest, Rsvp $rsvp, bool $isNew): void
    {
        if ($guest->email) {
            SendRsvpConfirmation::dispatch($guest, $event, $rsvp);
        }

        if ($isNew) {
            $this->notifService->notifyHost($event, 'rsvp', [
                'guest_name'  => $guest->name,
                'rsvp_status' => $rsvp->status,
                'guest_id'    => $guest->id,
                'event_id'    => $event->id,
            ]);
        }
    }
}

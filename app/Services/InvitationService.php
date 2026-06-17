<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Guest;
use App\Models\MusicLibrary;
use Illuminate\Support\Facades\Storage;

class InvitationService
{
    public function resolveEvent(string $slug): Event
    {
        $event = Event::with('template')
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (!$event) {
            abort(404);
        }

        if ($event->expires_at && $event->expires_at->isPast()) {
            abort(response()->view('errors.invitation-expired', ['event' => $event], 410));
        }

        return $event;
    }

    public function resolveGuest(?string $token, Event $event): ?Guest
    {
        if (!$token) return null;

        return Guest::where('token', $token)->where('event_id', $event->id)->first();
    }

    public function resolveMusic(Event $event): array
    {
        return match ($event->music_type) {
            'library'    => $this->libraryMusic($event),
            'soundcloud' => ['type' => 'soundcloud', 'url' => $event->music_source],
            'upload'     => ['type' => 'upload', 'url' => Storage::url($event->music_source)],
            default      => ['type' => 'none'],
        };
    }

    public function buildOgMeta(Event $event): array
    {
        $image = $event->og_image_path
            ? Storage::url($event->og_image_path)
            : asset('images/og-default.jpg');

        return [
            'title'        => $event->title . ' — Thiệp mời',
            'description'  => "Trân trọng kính mời bạn tới {$event->venue_name} vào ngày {$event->event_date->format('d/m/Y')}.",
            'url'          => route('invitation.show', $event->slug),
            'image'        => $image,
            'image_width'  => 1200,
            'image_height' => 630,
        ];
    }

    public function generateIcs(Event $event): string
    {
        $dateStr = $event->event_date->format('Ymd');
        $start   = $event->event_time
            ? $dateStr . 'T' . str_replace(':', '', substr((string) $event->event_time, 0, 5)) . '00'
            : $dateStr;

        $uid = $event->slug . '@invia.vn';

        return implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Invia.vn//Thiep moi//VI',
            'BEGIN:VEVENT',
            "UID:{$uid}",
            "DTSTART:{$start}",
            "SUMMARY:{$event->title}",
            "LOCATION:{$event->venue_name}, {$event->venue_address}",
            "DESCRIPTION:" . route('invitation.show', $event->slug),
            'END:VEVENT',
            'END:VCALENDAR',
        ]);
    }

    private function libraryMusic(Event $event): array
    {
        if (!$event->music_source) return ['type' => 'none'];

        $track = MusicLibrary::find((int) $event->music_source);

        return $track
            ? ['type' => 'library', 'url' => Storage::url($track->file_path), 'title' => $track->title]
            : ['type' => 'none'];
    }
}

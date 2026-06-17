<?php

namespace App\Services;

use App\Imports\GuestsImport;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Rsvp;
use App\Repositories\GuestRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GuestService
{
    public function __construct(private GuestRepository $guestRepo) {}

    public function addGuest(Event $event, array $data): Guest
    {
        return $this->guestRepo->create([
            'event_id' => $event->id,
            'name'     => $data['name'],
            'email'    => $data['email']    ?? null,
            'phone'    => $data['phone']    ?? null,
            'table_no' => $data['table_no'] ?? null,
            'token'    => Str::random(32),
            'source'   => 'manual',
        ]);
    }

    public function importExcel(Event $event, UploadedFile $file): array
    {
        $import = new GuestsImport($event);
        $import->import($file->getPathname());

        return [
            'imported' => $import->importedCount,
            'skipped'  => $import->skippedCount,
            'errors'   => $import->errors,
        ];
    }

    public function getStats(Event $event): array
    {
        $total     = Guest::where('event_id', $event->id)->count();
        $withEmail = Guest::where('event_id', $event->id)->whereNotNull('email')->count();
        $rsvpYes   = Rsvp::where('event_id', $event->id)->where('status', 'yes')->count();
        $rsvpNo    = Rsvp::where('event_id', $event->id)->where('status', 'no')->count();
        $rsvpMaybe = Rsvp::where('event_id', $event->id)->where('status', 'maybe')->count();
        $noRsvp    = $total - ($rsvpYes + $rsvpNo + $rsvpMaybe);

        return compact('total', 'withEmail', 'rsvpYes', 'rsvpNo', 'rsvpMaybe', 'noRsvp');
    }
}

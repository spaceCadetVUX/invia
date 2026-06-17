<?php

namespace App\Services;

use App\Exports\GuestsExport;
use App\Exports\WishesExport;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Rsvp;
use App\Models\Wish;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupService
{
    public function generate(Event $event, string $token): string
    {
        $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "backup-{$token}";
        mkdir($tmpDir, 0755, true);

        try {
            $this->writeGuestsExcel($event, $tmpDir);
            $this->writeWishesExcel($event, $tmpDir);
            $this->writeEventSummaryPdf($event, $tmpDir);
            $this->writeWishesBookPdf($event, $tmpDir);
            $this->writeThiepPdf($event, $tmpDir);
            $this->writeThiepHtml($event, $tmpDir);

            return $this->createZip($event, $token, $tmpDir);
        } finally {
            $this->rrmdir($tmpDir);
        }
    }

    private function writeGuestsExcel(Event $event, string $dir): void
    {
        (new GuestsExport($event))->saveToFile($dir . '/guests.xlsx');
    }

    private function writeWishesExcel(Event $event, string $dir): void
    {
        (new WishesExport($event))->save($dir . '/wishes.xlsx');
    }

    private function writeEventSummaryPdf(Event $event, string $dir): void
    {
        $rsvpStats = [
            'yes'   => Rsvp::where('event_id', $event->id)->where('status', 'yes')->count(),
            'no'    => Rsvp::where('event_id', $event->id)->where('status', 'no')->count(),
            'maybe' => Rsvp::where('event_id', $event->id)->where('status', 'maybe')->count(),
            'total' => Guest::where('event_id', $event->id)->count(),
        ];

        Pdf::loadView('pdf.event-summary', compact('event', 'rsvpStats'))
            ->save($dir . '/event-summary.pdf');
    }

    private function writeWishesBookPdf(Event $event, string $dir): void
    {
        $wishes = Wish::where('event_id', $event->id)
            ->where('is_hidden', false)
            ->with('guest:id,name')
            ->orderByDesc('is_pinned')
            ->orderBy('created_at')
            ->get();

        Pdf::loadView('pdf.wishes-book', compact('event', 'wishes'))
            ->save($dir . '/wishes-book.pdf');
    }

    private function writeThiepPdf(Event $event, string $dir): void
    {
        Pdf::loadView('pdf.thiep', compact('event'))
            ->setPaper('a4', 'portrait')
            ->save($dir . '/thiep.pdf');
    }

    private function writeThiepHtml(Event $event, string $dir): void
    {
        $event->loadMissing('template');

        $templateHtml = view("templates.{$event->template->blade_file}.index", [
            'event'     => $event,
            'guest'     => null,
            'music'     => ['type' => 'none'],
            'ogMeta'    => [],
            'isPreview' => false,
        ])->render();

        $cssPath = public_path("templates/{$event->template->blade_file}/style.css");
        $css     = file_exists($cssPath) ? file_get_contents($cssPath) : '';

        $jsPath = public_path("templates/{$event->template->blade_file}/script.js");
        $js     = file_exists($jsPath) ? file_get_contents($jsPath) : '';

        $gsapCdn = 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js';
        $title   = e($event->title);

        $selfContained = <<<HTML
        <!DOCTYPE html>
        <html lang="vi">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{$title} — Thiệp mời</title>
        <style>{$css}</style>
        </head>
        <body>
        {$templateHtml}
        <script src="{$gsapCdn}"></script>
        <script>{$js}</script>
        </body>
        </html>
        HTML;

        file_put_contents($dir . '/thiep.html', $selfContained);
    }

    private function createZip(Event $event, string $token, string $dir): string
    {
        $zipName = "backups/{$event->id}-{$token}.zip";
        $tmpZip  = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "{$token}.zip";

        $zip = new ZipArchive();
        $zip->open($tmpZip, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach (glob($dir . DIRECTORY_SEPARATOR . '*') as $file) {
            $zip->addFile($file, basename($file));
        }

        $zip->close();

        Storage::put($zipName, file_get_contents($tmpZip));
        unlink($tmpZip);

        return $zipName;
    }

    private function rrmdir(string $dir): void
    {
        if (!is_dir($dir)) return;
        foreach (glob($dir . DIRECTORY_SEPARATOR . '*') ?: [] as $f) {
            is_dir($f) ? $this->rrmdir($f) : unlink($f);
        }
        rmdir($dir);
    }
}

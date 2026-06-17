<?php

namespace App\Exports;

use App\Models\Event;
use App\Models\Guest;
use Illuminate\Support\Facades\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GuestsExport
{
    public function __construct(private Event $event) {}

    private function buildSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $headings = ['Tên', 'Email', 'SĐT', 'Bàn', 'RSVP', 'Người đi cùng', 'Link thiệp cá nhân'];
        $sheet->fromArray([$headings], null, 'A1');

        $guests = Guest::where('event_id', $this->event->id)
            ->with('rsvp:guest_id,status,plus_one')
            ->get();

        $rowIndex = 2;
        foreach ($guests as $g) {
            $link = route('invitation.show', $this->event->slug) . '?t=' . $g->token;
            $sheet->fromArray([[
                $g->name,
                $g->email,
                $g->phone,
                $g->table_no,
                $g->rsvp?->status ?? 'chưa xác nhận',
                $g->rsvp?->plus_one ?? 0,
                $link,
            ]], null, "A{$rowIndex}");
            $rowIndex++;
        }

        return $spreadsheet;
    }

    public function saveToFile(string $filePath): void
    {
        (new Xlsx($this->buildSpreadsheet()))->save($filePath);
    }

    public function download(string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $spreadsheet = $this->buildSpreadsheet();

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}

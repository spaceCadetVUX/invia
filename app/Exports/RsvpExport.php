<?php

namespace App\Exports;

use App\Models\Event;
use App\Models\Rsvp;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RsvpExport
{
    public function __construct(private Event $event) {}

    public function download(string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $sheet->fromArray(
            [['Tên', 'Email', 'SĐT', 'Tham dự', 'Người đi cùng', 'Yêu cầu ăn uống', 'Bàn', 'Thời gian RSVP']],
            null, 'A1'
        );

        $rsvps = Rsvp::where('event_id', $this->event->id)
            ->with('guest:id,name,email,phone,table_no')
            ->orderBy('status')
            ->get();

        $rowIndex = 2;
        foreach ($rsvps as $r) {
            $sheet->fromArray([[
                $r->guest->name,
                $r->guest->email,
                $r->guest->phone,
                match ($r->status) {
                    'yes'   => 'Tham dự',
                    'no'    => 'Từ chối',
                    'maybe' => 'Có thể',
                    default => '—',
                },
                $r->plus_one ?? 0,
                $r->dietary,
                $r->guest->table_no,
                $r->created_at->format('d/m/Y H:i'),
            ]], null, "A{$rowIndex}");
            $rowIndex++;
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}

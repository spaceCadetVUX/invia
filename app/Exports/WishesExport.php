<?php

namespace App\Exports;

use App\Models\Event;
use App\Models\Wish;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class WishesExport
{
    public function __construct(private Event $event) {}

    public function save(string $filePath): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $sheet->fromArray(
            [['Tên khách', 'Lời chúc', 'Nổi bật', 'Thời gian']],
            null, 'A1'
        );

        $wishes = Wish::where('event_id', $this->event->id)
            ->where('is_hidden', false)
            ->with('guest:id,name')
            ->orderByDesc('is_pinned')
            ->orderBy('created_at')
            ->get();

        $rowIndex = 2;
        foreach ($wishes as $w) {
            $sheet->fromArray([[
                $w->guest->name,
                $w->message,
                $w->is_pinned ? 'Có' : '',
                $w->created_at->format('d/m/Y H:i'),
            ]], null, "A{$rowIndex}");
            $rowIndex++;
        }

        (new Xlsx($spreadsheet))->save($filePath);
    }
}

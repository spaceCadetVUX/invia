<?php

namespace App\Imports;

use App\Models\Event;
use App\Models\Guest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GuestsImport
{
    public int   $importedCount = 0;
    public int   $skippedCount  = 0;
    public array $errors        = [];

    public function __construct(public Event $event) {}

    public function import(string $filePath): void
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, false);

        if (empty($rows)) return;

        // Row đầu là heading — normalize về lowercase, bỏ dấu cách
        $headings = array_map(fn ($h) => strtolower(trim((string) $h)), $rows[0]);

        // Map heading index
        $colMap = [];
        foreach (['ten', 'email', 'sdt', 'ban'] as $col) {
            $idx = array_search($col, $headings);
            $colMap[$col] = $idx !== false ? $idx : null;
        }

        foreach (array_slice($rows, 1) as $rowIndex => $row) {
            $rowNum = $rowIndex + 2;

            $data = [
                'ten'   => isset($colMap['ten'])   && $colMap['ten'] !== null ? trim((string) ($row[$colMap['ten']] ?? '')) : '',
                'email' => isset($colMap['email']) && $colMap['email'] !== null ? trim((string) ($row[$colMap['email']] ?? '')) : null,
                'sdt'   => isset($colMap['sdt'])   && $colMap['sdt'] !== null ? trim((string) ($row[$colMap['sdt']] ?? '')) : null,
                'ban'   => isset($colMap['ban'])    && $colMap['ban'] !== null ? trim((string) ($row[$colMap['ban']] ?? '')) : null,
            ];

            // Bỏ qua dòng trống
            if ($data['ten'] === '') {
                continue;
            }

            $validator = Validator::make($data, [
                'ten'   => ['required', 'string', 'max:100'],
                'email' => ['nullable', 'email', 'max:150'],
                'sdt'   => ['nullable', 'string', 'max:20'],
                'ban'   => ['nullable', 'string', 'max:20'],
            ]);

            if ($validator->fails()) {
                $this->errors[] = ['row' => $rowNum, 'message' => implode(', ', $validator->errors()->all())];
                $this->skippedCount++;
                continue;
            }

            Guest::create([
                'event_id' => $this->event->id,
                'name'     => $data['ten'],
                'email'    => $data['email'] ?: null,
                'phone'    => $data['sdt']   ?: null,
                'table_no' => $data['ban']   ?: null,
                'token'    => Str::random(32),
                'source'   => 'import',
            ]);

            $this->importedCount++;
        }
    }
}

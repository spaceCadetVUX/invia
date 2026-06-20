<?php

namespace App\Console\Commands;

use App\Models\MusicLibrary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackfillMusicDuration extends Command
{
    protected $signature   = 'music:backfill-duration';
    protected $description = 'Scan music files with getID3 and populate missing duration values';

    public function handle(): int
    {
        $tracks = MusicLibrary::where(fn($q) => $q->whereNull('duration')->orWhere('duration', 0))->get();

        if ($tracks->isEmpty()) {
            $this->info('All tracks already have duration.');
            return 0;
        }

        $getID3  = new \getID3();
        $updated = 0;

        foreach ($tracks as $track) {
            $path = Storage::disk()->path($track->file_path);

            if (!file_exists($path)) {
                $this->warn("MISSING file: {$track->title}");
                continue;
            }

            $info = $getID3->analyze($path);
            $dur  = isset($info['playtime_seconds']) ? (int) $info['playtime_seconds'] : null;

            if ($dur > 0) {
                $track->update(['duration' => $dur]);
                $updated++;
                $this->line("  ✓ {$track->title} → {$dur}s");
            } else {
                $this->warn("  ✗ {$track->title}: no metadata");
            }
        }

        $this->info("Done: {$updated}/{$tracks->count()} updated.");
        return 0;
    }
}

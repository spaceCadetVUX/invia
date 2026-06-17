<?php

namespace App\Console\Commands;

use App\Models\EventBackup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanExpiredBackups extends Command
{
    protected $signature   = 'backups:clean';
    protected $description = 'Delete expired event backup zip files and records';

    public function handle(): int
    {
        $expired = EventBackup::query()
            ->where('status', 'ready')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $backup) {
            if ($backup->zip_path && Storage::exists($backup->zip_path)) {
                Storage::delete($backup->zip_path);
            }
            $backup->delete();
        }

        // Clean up old failed / pending records older than 2 days
        EventBackup::query()
            ->whereIn('status', ['failed', 'pending'])
            ->where('created_at', '<', now()->subDays(2))
            ->delete();

        $this->info("Cleaned {$expired->count()} expired backups.");

        return Command::SUCCESS;
    }
}

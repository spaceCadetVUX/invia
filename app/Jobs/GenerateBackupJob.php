<?php

namespace App\Jobs;

use App\Mail\BackupReadyMail;
use App\Models\EventBackup;
use App\Services\BackupService;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GenerateBackupJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(public EventBackup $backup) {}

    public function handle(BackupService $service, NotificationService $notifService): void
    {
        $event = $this->backup->event()->with(['template', 'user'])->firstOrFail();

        try {
            $zipPath = $service->generate($event, $this->backup->token);

            $this->backup->update([
                'status'     => 'ready',
                'zip_path'   => $zipPath,
                'expires_at' => now()->addHours(24),
            ]);

            $notifService->notifyHost($event, 'system', [
                'title'        => __('backup.ready_title'),
                'download_url' => route('dashboard.events.backup.download', [$event->slug, $this->backup->token]),
                'event_id'     => $event->id,
            ]);

            Mail::to($event->user->email)->send(new BackupReadyMail($event, $this->backup));

        } catch (\Throwable $e) {
            $this->backup->update(['status' => 'failed']);
            Log::error("Backup failed event {$event->id}: " . $e->getMessage());
            throw $e;
        }
    }
}

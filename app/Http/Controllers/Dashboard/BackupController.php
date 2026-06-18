<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateBackupJob;
use App\Models\Event;
use App\Models\EventBackup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function create(Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        $existing = EventBackup::where('event_id', $event->id)
            ->whereIn('status', ['pending', 'ready'])
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latest()
            ->first();

        if ($existing?->status === 'ready') {
            return response()->json([
                'status'       => 'ready',
                'download_url' => route('dashboard.events.backup.download', [$event->slug, $existing->token]),
                'expires_at'   => $existing->expires_at,
            ]);
        }

        if ($existing?->status === 'pending') {
            return response()->json(['status' => 'pending', 'message' => __('backup.already_processing')]);
        }

        $backup = EventBackup::create([
            'event_id' => $event->id,
            'token'    => Str::random(48),
            'status'   => 'pending',
        ]);

        GenerateBackupJob::dispatch($backup);

        return response()->json(['status' => 'pending', 'message' => __('backup.queued')], 202);
    }

    public function status(Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        $backup = EventBackup::where('event_id', $event->id)->latest()->first();

        if (!$backup) {
            return response()->json(['status' => 'none']);
        }

        return response()->json([
            'status'       => $backup->status,
            'download_url' => $backup->status === 'ready'
                ? route('dashboard.events.backup.download', [$event->slug, $backup->token])
                : null,
            'expires_at'   => $backup->expires_at,
        ]);
    }

    public function download(Event $event, string $token): RedirectResponse|StreamedResponse
    {
        $this->authorize('update', $event);

        $backup = EventBackup::where('event_id', $event->id)
            ->where('token', $token)
            ->where('status', 'ready')
            ->firstOrFail();

        abort_if($backup->expires_at && $backup->expires_at->isPast(), 410, __('backup.link_expired'));

        // R2 / S3: dùng signed URL (redirect, không stream qua server)
        // local: fallback về stream vì local disk không hỗ trợ temporaryUrl
        try {
            $signedUrl = Storage::disk()->temporaryUrl($backup->zip_path, now()->addMinutes(5));
            return redirect()->away($signedUrl);
        } catch (\RuntimeException) {
            return Storage::disk()->download($backup->zip_path, "invia-backup-{$event->slug}.zip");
        }
    }
}

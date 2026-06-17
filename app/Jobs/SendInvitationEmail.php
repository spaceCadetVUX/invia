<?php

namespace App\Jobs;

use App\Mail\InvitationMail;
use App\Models\Event;
use App\Models\Guest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendInvitationEmail implements ShouldQueue
{
    use Queueable;

    public int $tries  = 3;
    public int $backoff = 60;

    public function __construct(
        public Event $event,
        public Guest $guest,
    ) {}

    public function handle(): void
    {
        $this->guest->refresh();

        if ($this->guest->unsubscribed_at) {
            return;
        }

        Mail::to($this->guest->email)->send(new InvitationMail($this->event, $this->guest));

        $this->guest->update(['email_sent_at' => now()]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error("SendInvitationEmail failed: guest {$this->guest->id}", [
            'event_id' => $this->event->id,
            'error'    => $e->getMessage(),
        ]);
    }
}

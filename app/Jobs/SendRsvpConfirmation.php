<?php

namespace App\Jobs;

use App\Mail\RsvpConfirmationMail;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Rsvp;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendRsvpConfirmation implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public Guest $guest,
        public Event $event,
        public Rsvp  $rsvp,
    ) {}

    public function handle(): void
    {
        Mail::to($this->guest->email)->send(new RsvpConfirmationMail(
            guest: $this->guest,
            event: $this->event,
            rsvp:  $this->rsvp,
        ));
    }
}

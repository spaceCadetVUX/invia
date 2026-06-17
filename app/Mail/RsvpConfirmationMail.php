<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Guest;
use App\Models\Rsvp;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RsvpConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Guest $guest,
        public Event $event,
        public Rsvp  $rsvp,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Xác nhận tham dự — ' . $this->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.rsvp-confirmation');
    }
}

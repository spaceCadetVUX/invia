<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Guest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Event $event,
        public Guest $guest,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('email.invitation_subject', ['title' => $this->event->title]),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.invitation');
    }

    public function headers(): Headers
    {
        $unsubUrl = route('unsubscribe', $this->guest->token);

        return new Headers(
            text: [
                'List-Unsubscribe'      => "<{$unsubUrl}>",
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ]
        );
    }
}

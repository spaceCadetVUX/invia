<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Event   $event,
        public Payment $payment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thanh toán thành công — ' . $this->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payment-confirm');
    }
}

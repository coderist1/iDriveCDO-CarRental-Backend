<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingNoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking, public string $kind)
    {
        $this->booking->loadMissing(['user', 'vehicle', 'pickupLocation', 'dropoffLocation']);
    }

    public function envelope(): Envelope
    {
        $subject = match ($this->kind) {
            'confirmed' => 'Booking Confirmation '.$this->booking->ref,
            'return_requested' => 'Return requested '.$this->booking->ref,
            default => 'Reservation Confirmation '.$this->booking->ref,
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.booking-notice');
    }
}

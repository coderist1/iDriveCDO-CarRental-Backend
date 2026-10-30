<?php

namespace App\Services;

use App\Mail\BookingNoticeMail;
use App\Models\Booking;
use Illuminate\Support\Facades\Mail;
use Throwable;

class BookingNotifier
{
    public function reservation(Booking $booking): void
    {
        $this->send($booking, 'reserved');
    }

    public function confirmation(Booking $booking): void
    {
        $this->send($booking, 'confirmed');
    }

    private function send(Booking $booking, string $kind): void
    {
        $booking->loadMissing('user');
        $email = $booking->user?->email;
        if (! $email) {
            return;
        }

        try {
            Mail::to($email)->send(new BookingNoticeMail($booking, $kind));
        } catch (Throwable $e) {
            report($e);
        }
    }
}

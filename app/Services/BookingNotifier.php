<?php

namespace App\Services;

use App\Mail\BookingNoticeMail;
use App\Models\Booking;
use App\Models\User;
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

    /** Tell admin and staff a renter asked to return the car. */
    public function returnRequested(Booking $booking): void
    {
        $booking->loadMissing(['user', 'vehicle', 'pickupLocation', 'dropoffLocation']);
        $emails = User::query()
            ->whereIn('role', ['admin', 'staff'])
            ->where('status', 'active')
            ->pluck('email')
            ->filter()
            ->unique()
            ->values();

        foreach ($emails as $email) {
            try {
                Mail::to($email)->send(new BookingNoticeMail($booking, 'return_requested'));
            } catch (Throwable $e) {
                report($e);
            }
        }
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

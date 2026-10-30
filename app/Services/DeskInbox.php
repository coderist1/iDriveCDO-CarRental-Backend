<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\MessageThread;
use App\Models\ThreadMessage;
use App\Models\User;

class DeskInbox
{
    public function notifyCustomer(User $customer, string $topic, string $body, ?int $fromUserId = null): MessageThread
    {
        $thread = MessageThread::create([
            'kind' => 'notice',
            'topic' => mb_substr($topic, 0, 80),
            'customer_id' => $customer->user_id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'status' => 'open',
            'unread_staff' => false,
            'unread_customer' => true,
        ]);

        ThreadMessage::create([
            'thread_id' => $thread->thread_id,
            'from_role' => 'staff',
            'from_user_id' => $fromUserId,
            'from_name' => 'iDrive desk',
            'body' => $body,
            'sent_at' => now(),
        ]);

        return $thread->refresh();
    }

    /**
     * Open or refresh the desk inbox thread when a renter asks to return the car.
     */
    public function returnRequested(Booking $booking, ?string $notes = null): MessageThread
    {
        $booking->loadMissing(['user', 'vehicle', 'dropoffLocation']);
        $customer = $booking->user;
        $name = trim((string) ($customer?->name ?: 'Customer')) ?: 'Customer';

        $thread = MessageThread::query()->firstOrNew(['booking_id' => $booking->booking_id]);
        $thread->fill([
            'kind' => 'return',
            'topic' => mb_substr('Return · '.$booking->ref, 0, 80),
            'customer_id' => $customer?->user_id,
            'customer_name' => mb_substr($name, 0, 80),
            'customer_email' => $customer?->email,
            'customer_phone' => $customer?->phone,
            'status' => 'open',
            'unread_staff' => true,
            'unread_customer' => false,
        ]);
        $thread->save();

        $dropoff = $booking->dropoffLocation?->name ?: 'the return location';
        $body = 'Customer requested vehicle return for '.$booking->ref
            .' at '.$dropoff
            .'. Please accept the return at the desk.';
        if ($notes = trim((string) $notes)) {
            $body .= ' Notes: '.mb_substr($notes, 0, 200);
        }

        ThreadMessage::create([
            'thread_id' => $thread->thread_id,
            'from_role' => 'customer',
            'from_user_id' => $customer?->user_id,
            'from_name' => mb_substr($name, 0, 80),
            'body' => mb_substr($body, 0, 500),
            'sent_at' => now(),
        ]);

        return $thread->refresh()->load('messages');
    }
}

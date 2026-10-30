<?php

namespace App\Services;

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
}

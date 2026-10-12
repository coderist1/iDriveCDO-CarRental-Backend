<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetCode extends Notification
{
    public function __construct(public string $code) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $company = config('app.name');

        return (new MailMessage)
            ->subject(__('Your password reset code'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('Use this code to reset the password for your :company account:', ['company' => $company]))
            ->line($this->code)
            ->line(__('This code expires in :count minutes and can only be used once.', ['count' => 10]))
            ->line(__('If you did not request a password reset, you can safely ignore this email.'))
            ->salutation(__('Thank you,')."  \n".$company);
    }
}

<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailAddress extends VerifyEmail
{
    /**
     * Build the branded verification mail for the given notifiable.
     *
     * The signed URL is produced by the parent's verificationUrl(), which uses
     * URL::temporarySignedRoute() and honours auth.verification.expire.
     */
    public function toMail($notifiable): MailMessage
    {
        $company = config('app.name');
        $expiresAfter = config('auth.verification.expire', 60);

        return (new MailMessage)
            ->subject(__('Verify Your Email Address'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('Thank you for creating an account with :company.', ['company' => $company]))
            ->line(__('Please verify your email address to activate your account.'))
            ->action(__('Verify Email'), $this->verificationUrl($notifiable))
            ->line(__('This link will expire in :count minutes.', ['count' => $expiresAfter]))
            ->line(__('If you did not create this account, you can safely ignore this email.'))
            ->salutation(__('Thank you,')."  \n".$company);
    }
}

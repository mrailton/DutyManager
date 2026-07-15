<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;

class UserInvited extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $token = Password::createToken($notifiable);

        return (new MailMessage())
            ->subject('You have been invited to Duty Manager')
            ->line('An account has been created for you on Duty Manager.')
            ->action('Set Your Password', route('password.reset', ['token' => $token, 'email' => $notifiable->email]))
            ->line('This link will expire in 60 minutes.')
            ->line('If you did not expect this invitation, you can ignore this email.');
    }
}

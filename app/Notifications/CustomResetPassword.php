<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomResetPassword extends Notification
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/reset-password/'.$this->token.'?email='.$notifiable->email);

        return (new MailMessage)
            ->subject('Reset Your Password - Hall Ease')
            ->greeting('Hello 👋')
            ->line('We received a request to reset your password.')
            ->action('Reset Password', $url)
            ->line('This link will expire in 60 minutes.')
            ->line('If you did not request this, please ignore this email.')
            ->salutation('Regards, Hall Ease Team 💙');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
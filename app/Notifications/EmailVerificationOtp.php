<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerificationOtp extends Notification
{
    use Queueable;

    public string $otp;
    public string $userName;

    public function __construct(string $otp, string $userName)
    {
        $this->otp = $otp;
        $this->userName = $userName;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('HallEase – Verify Your Email Address')
            ->greeting("Hello {$this->userName}!")
            ->line('Thank you for registering on HallEase.')
            ->line('Please use the OTP below to verify your email address. It is valid for **2 minutes**.')
            ->line('')
            ->line("## Your OTP: **{$this->otp}**")
            ->line('')
            ->line('If you did not request this, please ignore this email.')
            ->salutation('— HallEase Team');
    }
}

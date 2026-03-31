<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMediaUserRegistered extends Notification
{
    use Queueable;

    protected $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Media User Registration Request')
            ->greeting('Hello Admin,')
            ->line('A new media user has registered on HallEase and is waiting for your verification.')
            ->line('User Details:')
            ->line('Name: ' . $this->user->name)
            ->line('Email: ' . $this->user->email)
            ->action('View Verification Requests', route('admin.verifications.index'))
            ->line('Please review the request and approve or reject it accordingly.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'user_id' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'message' => 'New media user registration: ' . $this->user->name . ' (' . $this->user->email . ')',
            'type' => 'media_registration',
        ];
    }
}

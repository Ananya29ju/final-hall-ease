<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountStatusUpdated extends Notification
{
    use Queueable;

    protected $user;
    protected $status;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, string $status)
    {
        $this->user = $user;
        $this->status = $status;
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
        if ($this->status === 'approved') {
            return (new MailMessage)
                ->subject('Account Approved - HallEase')
                ->greeting('Hello ' . $this->user->name . ',')
                ->line('Congratulations! Your media account has been approved by the administrator.')
                ->line('You can now log in and manage your media requirements for bookings.')
                ->action('Login Now', route('login'))
                ->line('Welcome to HallEase!');
        } else {
            return (new MailMessage)
                ->subject('Account Status Update - HallEase')
                ->greeting('Hello ' . $this->user->name . ',')
                ->line('We regret to inform you that your media account request has been rejected.')
                ->line('Please contact the administration for any further information.')
                ->line('Thank you.');
        }
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        $message = $this->status === 'approved' 
            ? 'Your account has been approved! You can now log in.' 
            : 'Your account request has been rejected. Please contact the administrator.';

        return [
            'user_id' => $this->user->id,
            'status' => $this->status,
            'message' => $message,
            'type' => 'account_verification',
        ];
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WaitlistSlotAvailable extends Notification
{
    use Queueable;

    public $waitlist;

    /**
     * Create a new notification instance.
     */
    public function __construct($waitlist)
    {
        $this->waitlist = $waitlist;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $hallName = $this->waitlist->hall->name ?? 'the hall';
        $message = "A slot has opened up for {$hallName} for your waitlisted request '{$this->waitlist->event_name}'. Please confirm your booking immediately. This offer expires at {$this->waitlist->expires_at->format('H:i')}.";

        return [
            'waitlist_id' => $this->waitlist->id,
            'event_name' => $this->waitlist->event_name,
            'message' => $message,
            'type' => 'waitlist_available'
        ];
    }
}

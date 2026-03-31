<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBookingRequest extends Notification
{
    use Queueable;

    public $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $data = $this->toArray($notifiable);
        $message = $data['message'];

        return (new MailMessage)
            ->subject('New Booking Request - ' . $this->booking->event_name)
            ->greeting('Hello Admin,')
            ->line($message)
            ->action('Review Request', route('login'))
            ->line('Thank you!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $startFormatted = optional($this->booking->start_datetime)->format('d M Y h:i A');
        $endFormatted = optional($this->booking->end_datetime)->format('d M Y h:i A');

        return [
            'booking_id' => $this->booking->id,
            'event_name' => $this->booking->event_name,
            'start_datetime' => optional($this->booking->start_datetime)->format('Y-m-d H:i:s'),
            'end_datetime' => optional($this->booking->end_datetime)->format('Y-m-d H:i:s'),
            'message' => "New booking request: '{$this->booking->event_name}' from {$startFormatted} to {$endFormatted}.",
        ];
    }
}

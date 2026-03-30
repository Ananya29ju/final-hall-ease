<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingStatusUpdated extends Notification
{
    use Queueable;

    public $booking;
    public $statusType; // 'admin' or 'media'
    public $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct($booking, $statusType, $newStatus)
    {
        $this->booking = $booking;
        $this->statusType = $statusType;
        $this->newStatus = $newStatus;
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
        $message = "The status of your booking '{$this->booking->event_name}' has been updated to '{$this->newStatus}'.";

        if ($this->statusType === 'admin') {
            $message = "Administrator has updated your booking '{$this->booking->event_name}' to '{$this->newStatus}'.";
        } elseif ($this->statusType === 'admin_approved_media') {
            $message = "New media request: Administrator has approved booking '{$this->booking->event_name}' and requested media services.";
        } elseif ($this->statusType === 'media' || $this->statusType === 'media_confirmed') {
            if ($this->newStatus === 'accepted') {
                $message = "The Media Team has accepted your request for '{$this->booking->event_name}'.";
            } elseif ($this->newStatus === 'rejected') {
                $message = "Media request for '{$this->booking->event_name}' was rejected. Reason: " . ($this->booking->media_feedback_reason ?? 'N/A');
                if ($this->booking->unavailable_media_requirements) {
                    $message .= ". Unavailable: " . implode(', ', $this->booking->unavailable_media_requirements);
                }
            } elseif ($this->newStatus === 'kept_pending') {
                $message = "Media request for '{$this->booking->event_name}' is kept pending. Reason: " . ($this->booking->media_feedback_reason ?? 'N/A');
                if ($this->booking->unavailable_media_requirements) {
                    $message .= ". Issues with: " . implode(', ', $this->booking->unavailable_media_requirements);
                }
            }
        } elseif ($this->statusType === 'user' || $this->statusType === 'user_media') {
            $reason = $this->booking->cancellation_reason ? " Reason: {$this->booking->cancellation_reason}." : '';
            $message = "The staff/coordinator has cancelled the booking '{$this->booking->event_name}'." . $reason;
        }

        $data = [
            'booking_id' => $this->booking->id,
            'event_name' => $this->booking->event_name,
            'status_type' => $this->statusType,
            'new_status' => $this->newStatus,
            'message' => $message
        ];

        // Add extra feedback details if available
        if ($this->booking->media_feedback_reason) {
            $data['feedback_reason'] = $this->booking->media_feedback_reason;
            $data['unavailable_requirements'] = $this->booking->unavailable_media_requirements;
            $data['remarks'] = $this->booking->media_remarks;
        }

        return $data;
    }

}

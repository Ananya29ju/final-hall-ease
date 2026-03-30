<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    /**
     * Show notifications for logged-in user.
     */
    public function index()
    {
        $ownerColumn = $this->getBookingOwnerColumn();
        $bookingsTable = (new Booking())->getTable();
        $hasCancellationReason = Schema::hasColumn($bookingsTable, 'cancellation_reason');

        $baseQuery = Booking::with('hall');
        if ($ownerColumn) {
            $baseQuery->where($ownerColumn, Auth::id());
        } else {
            $baseQuery->whereRaw('1 = 0');
        }

        $bookingUpdates = (clone $baseQuery)
            ->when($hasCancellationReason, function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('cancellation_reason')
                        ->orWhere('cancellation_reason', '');
                });
            })
            ->latest('created_at')
            ->take(20)
            ->get();

        $cancellationNotifications = collect();
        if ($hasCancellationReason) {
            $cancellationNotifications = (clone $baseQuery)
                ->whereNotNull('cancellation_reason')
                ->where('cancellation_reason', '!=', '')
                ->latest('updated_at')
                ->take(20)
                ->get();
        }

        $upcomingReminders = (clone $baseQuery)
            ->where('start_datetime', '>=', now())
            ->where('start_datetime', '<=', now()->addDays(7))
            ->when($hasCancellationReason, function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('cancellation_reason')
                        ->orWhere('cancellation_reason', '');
                });
            })
            ->orderBy('start_datetime')
            ->take(20)
            ->get();

        $latestUpdates = collect();

        $latestUpdates = $latestUpdates->merge(
            $bookingUpdates->map(function (Booking $booking) {
                return [
                    'type' => 'Booking Submitted',
                    'type_class' => 'success',
                    'hall_name' => optional($booking->hall)->name ?? 'N/A',
                    'event_name' => $booking->event_name ?? 'N/A',
                    'details' => 'Hall booking request submitted successfully.',
                    'time' => $booking->created_at,
                ];
            })
        );

        $latestUpdates = $latestUpdates->merge(
            $cancellationNotifications->map(function (Booking $booking) {
                return [
                    'type' => 'Cancellation Update',
                    'type_class' => 'danger',
                    'hall_name' => optional($booking->hall)->name ?? 'N/A',
                    'event_name' => $booking->event_name ?? 'N/A',
                    'details' => $booking->cancellation_reason ?: 'Booking cancelled.',
                    'time' => $booking->updated_at,
                ];
            })
        );

        $latestUpdates = $latestUpdates->merge(
            $upcomingReminders->map(function (Booking $booking) {
                return [
                    'type' => 'Upcoming Reminder',
                    'type_class' => 'warning',
                    'hall_name' => optional($booking->hall)->name ?? 'N/A',
                    'event_name' => $booking->event_name ?? 'N/A',
                    'details' => 'Event scheduled: ' . $booking->formatted_datetime_range,
                    'time' => $booking->start_datetime,
                ];
            })
        );

        $latestUpdates = $latestUpdates
            ->sortByDesc('time')
            ->take(20)
            ->values();

        return view('user.notifications.index', [
            'latestUpdates' => $latestUpdates,
        ]);
    }

    /**
     * Resolve booking owner column name for mixed schemas.
     */
    private function getBookingOwnerColumn(): ?string
    {
        $table = (new Booking())->getTable();

        if (Schema::hasColumn($table, 'customer_id')) {
            return 'customer_id';
        }

        if (Schema::hasColumn($table, 'user_id')) {
            return 'user_id';
        }

        return null;
    }
}

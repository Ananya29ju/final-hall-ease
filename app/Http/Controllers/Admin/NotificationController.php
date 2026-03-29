<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    /**
     * Show admin notifications for bookings and cancellations.
     */
    public function index()
    {
        $bookingsTable = (new Booking())->getTable();
        $hasCancellationReason = Schema::hasColumn($bookingsTable, 'cancellation_reason');
        $hasBookingStatus = Schema::hasColumn($bookingsTable, 'booking_status');

        $newBookingsQuery = Booking::with(['hall', 'customer', 'user']);
        if ($hasBookingStatus) {
            $newBookingsQuery->where('booking_status', 'pending');
        }
        if ($hasCancellationReason) {
            $newBookingsQuery->where(function ($query) {
                $query->whereNull('cancellation_reason')
                    ->orWhere('cancellation_reason', '');
            });
        }
        $newBookings = $newBookingsQuery
            ->latest()
            ->take(30)
            ->get();

        $cancellationNotifications = collect();
        if ($hasCancellationReason) {
            $cancellationNotifications = Booking::with(['hall', 'customer', 'user'])
                ->when($hasBookingStatus, function ($query) {
                    $query->where('booking_status', 'cancelled');
                })
                ->whereNotNull('cancellation_reason')
                ->where('cancellation_reason', '!=', '')
                ->latest()
                ->take(30)
                ->get();
        }

        return view('admin.notifications.index', [
            'newBookings' => $newBookings,
            'cancellationNotifications' => $cancellationNotifications,
        ]);
    }
}

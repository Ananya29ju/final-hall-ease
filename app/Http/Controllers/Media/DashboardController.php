<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Display the media dashboard with recent notifications.
     */
    public function index()
    {
        $notifications = auth()->user()->notifications()->latest()->take(10)->get();
        $unreadCount = auth()->user()->unreadNotifications()->count();

        $bookingsTable = (new Booking())->getTable();
        $calendarQuery = Booking::with(['hall', 'customer', 'user'])
            ->whereNotNull('start_datetime');

        if (Schema::hasColumn($bookingsTable, 'cancellation_reason')) {
            $calendarQuery->where(function ($query) {
                $query->whereNull('cancellation_reason')
                    ->orWhere('cancellation_reason', '');
            });
        }

        $calendarBookings = $calendarQuery
            ->orderBy('start_datetime')
            ->get()
            ->map(function (Booking $booking) {
                return [
                    'date' => optional($booking->start_datetime)->format('Y-m-d'),
                    'start_time' => optional($booking->start_datetime)->format('H:i'),
                    'end_time' => optional($booking->end_datetime)->format('H:i'),
                    'end_date' => optional($booking->end_datetime)->format('Y-m-d'),
                    'event_name' => $booking->event_name ?: 'Event',
                    'hall_name' => optional($booking->hall)->name ?: 'Hall',
                    'booked_by' => $booking->coordinator_name ?: (optional($booking->customer)->name ?: (optional($booking->user)->name ?: 'N/A')),
                ];
            })
            ->values();

        $pendingMediaBookings = Booking::where('admin_status', 'approved')
            ->whereNotNull('media_requirements')
            ->where('media_requirements', '!=', '[]')
            ->where('booking_status', '!=', 'cancelled')
            ->where('media_status', '!=', 'accepted')
            ->count();

        return view('media.dashboard', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'calendar_bookings' => $calendarBookings,
            'pending_media' => $pendingMediaBookings,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Hall;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * DashboardController (Admin)
 * 
 * Handles the logic and data aggregation for the administrative dashboard,
 * displaying key metrics like total users, halls, bookings, and rendering 
 * the administrative calendar view.
 */
class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with aggregated statistics.
     * 
     * Retrieves all bookings for the calendar, checking database schema for
     * cancellation fields. Also gathers total counts for users, halls, and events 
     * to populate the admin overview panel.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
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

        $allUserBookings = Booking::with(['hall', 'customer', 'user'])
            ->latest('start_datetime')
            ->get();

        $data = [
            'total_users' => User::count(),
            'total_halls' => Hall::count(),
            'total_bookings' => Booking::count(),
            'total_events' => Booking::count(),
            'calendar_bookings' => $calendarBookings,
            'all_user_bookings' => $allUserBookings,
        ];

        return view('admin.dashboard', $data);
    }
}

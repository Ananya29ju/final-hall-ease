<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hall;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * DashboardController (User)
 * 
 * Handles the logic for displaying the regular user (staff) dashboard.
 * This includes retrieving their personal booking statistics, upcoming bookings,
 * available halls, and formatting data for a calendar view.
 */
class DashboardController extends Controller
{
    /**
     * Display the user dashboard.
     * 
     * Dynamically identifies the correct user column based on the database schema
     * (either 'customer_id' or 'user_id'), calculates booking stats, and retrieves
     * calendar events to render on the dashboard view.
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $bookingsTable = (new Booking())->getTable();

        $ownerColumn = null;
        if (Schema::hasColumn($bookingsTable, 'customer_id')) {
            $ownerColumn = 'customer_id';
        } elseif (Schema::hasColumn($bookingsTable, 'user_id')) {
            $ownerColumn = 'user_id';
        }

        $myBookingsQuery = Booking::query();
        $upcomingBookingsQuery = Booking::query();
        $calendarBookingsQuery = Booking::with(['hall', 'customer', 'user'])
            ->whereNotNull('start_datetime');

        if ($ownerColumn) {
            $myBookingsQuery->where($ownerColumn, $user->id);
            $upcomingBookingsQuery->where($ownerColumn, $user->id);
        } else {
            $myBookingsQuery->whereRaw('1 = 0');
            $upcomingBookingsQuery->whereRaw('1 = 0');
        }

        if (Schema::hasColumn($bookingsTable, 'cancellation_reason')) {
            $notCancelled = function ($query) {
                $query->whereNull('cancellation_reason')
                    ->orWhere('cancellation_reason', '');
            };

            $myBookingsQuery->where($notCancelled);
            $upcomingBookingsQuery->where($notCancelled);
            $calendarBookingsQuery->where($notCancelled);
        }

        $data = [
            'my_bookings' => $myBookingsQuery->count(),
            'upcoming_bookings' => $upcomingBookingsQuery
                ->where('start_datetime', '>=', now())
                ->count(),
            'available_halls' => Hall::where('status', 'available')->count(),
            'total_halls' => Hall::count(),
            'campus_names' => Hall::query()
                ->whereNotNull('campus_name')
                ->where('campus_name', '!=', '')
                ->distinct()
                ->orderBy('campus_name')
                ->pluck('campus_name'),
            'calendar_bookings' => $calendarBookingsQuery
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
                ->values(),
        ];

        return view('user.dashboard', $data);
    }
}

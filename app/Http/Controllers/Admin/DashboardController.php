<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Hall;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function index()
    {
        $bookingsTable = (new Booking())->getTable();
        $calendarQuery = Booking::with(['hall', 'customer', 'user'])
            ->whereNotNull('event_date');

        if (Schema::hasColumn($bookingsTable, 'cancellation_reason')) {
            $calendarQuery->where(function ($query) {
                $query->whereNull('cancellation_reason')
                    ->orWhere('cancellation_reason', '');
            });
        }

        $calendarBookings = $calendarQuery
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get()
            ->map(function (Booking $booking) {
                $formatTime = static function ($value): string {
                    if (blank($value)) {
                        return '--:--';
                    }

                    try {
                        return Carbon::parse($value)->format('H:i');
                    } catch (\Throwable $e) {
                        return (string) $value;
                    }
                };

                return [
                    'date' => optional($booking->event_date)->format('Y-m-d'),
                    'start_time' => $formatTime($booking->start_time),
                    'end_time' => $formatTime($booking->end_time),
                    'event_name' => $booking->event_name ?: 'Event',
                    'hall_name' => optional($booking->hall)->name ?: 'Hall',
                    'booked_by' => $booking->coordinator_name ?: (optional($booking->customer)->name ?: (optional($booking->user)->name ?: 'N/A')),
                ];
            })
            ->values();

        $allUserBookings = Booking::with(['hall', 'customer', 'user'])
            ->latest('event_date')
            ->latest('start_time')
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

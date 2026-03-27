<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hall;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Display user dashboard.
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
            ->whereNotNull('event_date');

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
                ->whereDate('event_date', '>=', now()->toDateString())
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
                ->values(),
        ];

        return view('user.dashboard', $data);
    }
}

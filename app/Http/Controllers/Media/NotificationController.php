<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    /**
     * Show media notifications for newly booked halls.
     */
    public function index()
    {
        return view('media.notifications.index', [
            'newBookings' => $this->newBookingsQuery()
                ->latest()
                ->take(30)
                ->get(),
        ]);
    }

    /**
     * Base query for bookings that media should be notified about.
     */
    private function newBookingsQuery()
    {
        $bookingsTable = (new Booking())->getTable();
        $query = Booking::with(['hall', 'customer', 'user']);

        if (Schema::hasColumn($bookingsTable, 'booking_status')) {
            $query->where('booking_status', 'confirmed');
        }

        if (Schema::hasColumn($bookingsTable, 'cancellation_reason')) {
            $query->where(function ($builder) {
                $builder->whereNull('cancellation_reason')
                    ->orWhere('cancellation_reason', '');
            });
        }

        return $query;
    }
}

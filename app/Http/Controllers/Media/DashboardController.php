<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Display the media dashboard with recent booking requests.
     */
    public function index()
    {
        $newBookings = $this->newBookingsQuery()
            ->latest()
            ->take(10)
            ->get();

        return view('media.dashboard', [
            'newBookings' => $newBookings,
            'newBookingCount' => $this->newBookingsQuery()->count(),
        ]);
    }

    /**
     * Base query for bookings that media should be notified about.
     */
    private function newBookingsQuery()
    {
        $query = Booking::with(['hall', 'customer', 'user'])
            ->where('admin_status', 'approved')
            ->whereNotNull('media_requirements')
            ->where('media_requirements', '!=', '[]')
            ->whereIn('booking_status', ['waiting for media', 'confirmed', 'confirmed without media']);

        if (Schema::hasColumn((new Booking())->getTable(), 'cancellation_reason')) {
            $query->where(function ($builder) {
                $builder->whereNull('cancellation_reason')
                    ->orWhere('cancellation_reason', '');
            });
        }

        return $query;
    }
}

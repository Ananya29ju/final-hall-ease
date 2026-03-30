<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Hall;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display reports dashboard
     */
    public function index()
    {
        $data = [
            'total_bookings' => Booking::count(),
            'completed_bookings' => Booking::where('booking_status', 'confirmed')->count(),
            'pending_bookings' => Booking::where('booking_status', 'pending')->count(),
            'total_halls' => Hall::count(),
            'occupied_halls' => Booking::where('booking_status', 'confirmed')
                ->distinct('hall_id')
                ->count('hall_id'),
            'total_customers' => User::where('role', 'user')->count(),
        ];

        return view('admin.reports.index', $data);
    }

    /**
     * Generate bookings report
     */
    public function bookings()
    {
        $bookings = Booking::with(['user', 'hall'])
            ->orderBy('start_datetime', 'desc')
            ->paginate(10);

        return view('admin.reports.bookings', compact('bookings'));
    }

    /**
     * Generate halls report
     */
    public function halls()
    {
        $hallStats = Hall::withCount('bookings')
            ->with('bookings')
            ->paginate(10);

        return view('admin.reports.halls', compact('hallStats'));
    }
}

<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Booking;
use App\Notifications\BookingStatusUpdated;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Display bookings that require media action.
     * Only shows bookings that are Admin-approved and have media requirements.
     */
    public function index(Request $request)
    {
        $query = Booking::with(['hall', 'customer', 'user'])
            ->where('admin_status', 'approved')
            ->whereNotNull('media_requirements')
            ->where('media_requirements', '!=', '[]')
            ->where('booking_status', '!=', 'cancelled');
            
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('hall', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('id', 'like', "%{$search}%");
        }

        $bookings = $query->latest()
            ->get();

        return view('media.bookings.index', compact('bookings'));
    }

    /**
     * Update the media approval status.
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'media_status' => 'required|in:accepted,rejected,kept_pending',
            'media_feedback_reason' => 'required_if:media_status,rejected,kept_pending|string|nullable',
            'unavailable_media_requirements' => 'required_if:media_status,rejected|array|nullable',
            'accepted_media_requirements' => 'nullable|array',
            'media_remarks' => 'nullable|string',
        ]);

        $mediaStatus = $request->media_status;
        $bookingStatus = $booking->booking_status;

        if ($mediaStatus === 'accepted') {
            $bookingStatus = 'confirmed';
        } elseif ($mediaStatus === 'rejected') {
            $bookingStatus = 'confirmed without media';
        } elseif ($mediaStatus === 'kept_pending') {
            $bookingStatus = 'waiting for media';
        }

        $booking->update([
            'media_status' => $mediaStatus,
            'booking_status' => $bookingStatus,
            'media_feedback_reason' => $request->media_feedback_reason,
            'unavailable_media_requirements' => $request->unavailable_media_requirements,
            'accepted_media_requirements' => $request->accepted_media_requirements,
            'media_remarks' => $request->media_remarks,
        ]);


        // 1. Notify the Staff (Owner)
        $staff = $booking->customer ?: $booking->user;
        if ($staff) {
            $staff->notify(new BookingStatusUpdated($booking, 'media', $mediaStatus));
        }

        // 2. Notify the Admin
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new BookingStatusUpdated($booking, 'media_confirmed', $mediaStatus));
        }

        return back()->with('success', 'Media action "' . ucfirst(str_replace('_', ' ', $mediaStatus)) . '" completed successfully.');
    }
}

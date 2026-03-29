<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hall;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class BookingController extends Controller
{
    /**
     * Display all bookings
     */
    public function index()
    {
        $bookingsTable = (new Booking())->getTable();
        $hasCancellationReason = Schema::hasColumn($bookingsTable, 'cancellation_reason');
        $today = now()->toDateString();

        $baseQuery = Booking::with(['hall', 'customer', 'user']);

        $upcomingQuery = clone $baseQuery;
        $upcomingQuery->whereDate('event_date', '>=', $today);
        if (Schema::hasColumn($bookingsTable, 'booking_status')) {
            $upcomingQuery->where('booking_status', '!=', 'cancelled');
        }
        if ($hasCancellationReason) {
            $upcomingQuery->where(function ($query) {
                $query->whereNull('cancellation_reason')
                    ->orWhere('cancellation_reason', '');
            });
        }

        $completedQuery = clone $baseQuery;
        $completedQuery->whereDate('event_date', '<', $today);
        if (Schema::hasColumn($bookingsTable, 'booking_status')) {
            $completedQuery->where('booking_status', '!=', 'cancelled');
        }
        if ($hasCancellationReason) {
            $completedQuery->where(function ($query) {
                $query->whereNull('cancellation_reason')
                    ->orWhere('cancellation_reason', '');
            });
        }

        $cancelledQuery = clone $baseQuery;
        if (Schema::hasColumn($bookingsTable, 'booking_status')) {
            $cancelledQuery->where('booking_status', 'cancelled');
        } elseif ($hasCancellationReason) {
            $cancelledQuery->whereNotNull('cancellation_reason')
                ->where('cancellation_reason', '!=', '');
        } else {
            $cancelledQuery->whereRaw('1 = 0');
        }

        return view('admin.bookings.index', [
            'upcomingBookings' => $upcomingQuery->latest('event_date')->latest('start_time')->get(),
            'completedBookings' => $completedQuery->latest('event_date')->latest('start_time')->get(),
            'cancelledBookings' => $cancelledQuery->latest('event_date')->latest('start_time')->get(),
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $halls = Hall::where('status', 'available')->get();
        $customers = User::where('role', 'user')->get();

        return view('admin.bookings.create', compact('halls', 'customers'));
    }

    /**
     * Store booking
     */
    public function store(Request $request)
    {
        $request->validate([
            'hall_id' => 'required|exists:halls,id',
            'customer_id' => 'required|exists:users,id',
            'event_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'event_name' => 'required|string|max:255',
            'event_department' => 'required|string|max:255',
            'event_type' => 'required|string|max:255',
            'coordinator_name' => 'required|string|max:255',
            'coordinator_phone' => 'required|string|max:20',
            'coordinator_department' => 'required|string|max:255',
            'coordinator_email' => 'required|email|max:255',
            'coordinator_emergency_number' => 'required|string|max:20',
            'media_requirements' => 'nullable|array',
            'media_requirements.*' => 'in:photography,videography,livestreaming,reels,photos,others',
            'media_requirements_other' => 'nullable|string|max:500',
            'resources' => 'nullable|array',
            'resources.*' => 'in:projectors,sound_systems,lighting,seating,other',
            'resources_other' => 'nullable|string|max:500',
            'details_confirmation' => 'accepted',
        ]);

        if (
            in_array('others', $request->input('media_requirements', []), true) &&
            blank($request->media_requirements_other)
        ) {
            return back()
                ->withErrors(['media_requirements_other' => 'Please specify the other media requirement.'])
                ->withInput();
        }

        if (
            in_array('other', $request->input('resources', []), true) &&
            blank($request->resources_other)
        ) {
            return back()
                ->withErrors(['resources_other' => 'Please specify the other resource requirement.'])
                ->withInput();
        }

        // 🔥 Check availability
        $exists = Booking::where('hall_id', $request->hall_id)
            ->where('event_date', $request->event_date)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time]);
            })
            ->exists();

        if ($exists) {
            return back()->with('error', 'Hall already booked for selected time.');
        }

        Booking::create([
            'hall_id' => $request->hall_id,
            'customer_id' => $request->customer_id,
            'created_by' => Auth::id(),
            'event_date' => $request->event_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'booking_status' => 'confirmed',
            'event_name' => $request->event_name,
            'event_department' => $request->event_department,
            'event_type' => $request->event_type,
            'coordinator_name' => $request->coordinator_name,
            'coordinator_phone' => $request->coordinator_phone,
            'coordinator_department' => $request->coordinator_department,
            'coordinator_email' => $request->coordinator_email,
            'coordinator_emergency_number' => $request->coordinator_emergency_number,
            'media_requirements' => $request->input('media_requirements', []),
            'media_requirements_other' => $request->media_requirements_other,
            'resources' => $request->input('resources', []),
            'resources_other' => $request->resources_other,
        ]);

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Booking created successfully.');
    }

    /**
     * Show single booking
     */
    public function show(Booking $booking)
    {
        $booking->load(['hall', 'customer', 'creator']);
        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Edit booking
     */
    public function edit(Booking $booking)
    {
        $halls = Hall::all();
        $customers = User::where('role', 'customer')->get();

        return view('admin.bookings.edit', compact('booking', 'halls', 'customers'));
    }

    /**
     * Update booking
     */
    public function update(Request $request, Booking $booking)
    {
        $request->validate([
            'hall_id' => 'required|exists:halls,id',
            'customer_id' => 'required|exists:users,id',
            'event_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'booking_status' => 'nullable|in:pending,confirmed,cancelled,completed',
            'cancellation_reason' => 'nullable|string',
            'resources' => 'nullable|array',
            'resources.*' => 'in:projectors,sound_systems,lighting,seating,other',
            'resources_other' => 'nullable|string|max:500',
        ]);

        if (
            in_array('other', $request->input('resources', []), true) &&
            blank($request->resources_other)
        ) {
            return back()
                ->withErrors(['resources_other' => 'Please specify the other resource requirement.'])
                ->withInput();
        }

        $booking->update([
            'hall_id' => $request->hall_id,
            'customer_id' => $request->customer_id,
            'event_date' => $request->event_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'booking_status' => $request->booking_status ?? $booking->booking_status,
            'cancellation_reason' => ($request->booking_status ?? $booking->booking_status) === 'cancelled'
                ? $request->cancellation_reason
                : null,
            'resources' => $request->input('resources', []),
            'resources_other' => $request->resources_other,
        ]);

        return redirect()
            ->route('admin.bookings.index')
            ->with('success', 'Booking updated successfully.');
    }

    /**
     * Delete booking
     */
    public function destroy(Booking $booking)
    {
        $booking->delete();

        return back()->with('success', 'Booking deleted successfully.');
    }

    /**
     * Update booking cancellation reason
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'booking_status' => 'required|in:pending,confirmed,cancelled,completed',
            'cancellation_reason' => 'nullable|string',
        ]);

        $booking->update([
            'booking_status' => $request->booking_status,
            'cancellation_reason' => $request->booking_status === 'cancelled'
                ? ($request->cancellation_reason ?: 'Cancelled by admin')
                : null,
        ]);

        return back()->with('success', 'Booking status updated.');
    }

    /**
     * Show booking cancellation form for admin.
     */
    public function showCancellationForm()
    {
        if (!Schema::hasColumn((new Booking())->getTable(), 'cancellation_reason')) {
            return back()->with('error', 'Cancellation reason field is not available yet. Please run latest migrations.');
        }

        $bookings = Booking::with(['hall', 'customer', 'user'])
            ->latest('event_date')
            ->latest('start_time')
            ->get();

        return view('admin.bookings.cancel', compact('bookings'));
    }

    /**
     * Submit cancellation reason for any booking as admin.
     */
    public function cancel(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
            'cancellation_reason_option' => 'required|in:postponded,event_cancelled,low_participation,other',
            'cancellation_reason_other' => 'nullable|string|max:500|required_if:cancellation_reason_option,other',
        ]);

        if (!Schema::hasColumn((new Booking())->getTable(), 'cancellation_reason')) {
            return back()
                ->with('error', 'Cancellation reason field is not available yet. Please run latest migrations.')
                ->withInput();
        }

        $booking = Booking::query()
            ->where('id', $validated['booking_id'])
            ->first();

        if (!$booking) {
            return back()->with('error', 'Invalid booking selected.')->withInput();
        }

        $reasonLabels = [
            'postponded' => 'Postponded',
            'event_cancelled' => 'Event Cancelled',
            'low_participation' => 'Low Participation',
            'other' => 'Other',
        ];

        $reasonText = $reasonLabels[$validated['cancellation_reason_option']] ?? 'Other';
        if ($validated['cancellation_reason_option'] === 'other') {
            $reasonText .= ': ' . ($validated['cancellation_reason_other'] ?? '');
        }

        $booking->update([
            'booking_status' => 'cancelled',
            'cancellation_reason' => $reasonText,
        ]);

        return redirect()
            ->route('admin.bookings.cancel.form')
            ->with('success', 'Booking cancellation details submitted successfully.');
    }
}

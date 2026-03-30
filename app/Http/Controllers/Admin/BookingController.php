<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hall;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use App\Notifications\BookingStatusUpdated;

class BookingController extends Controller
{
    /**
     * Display all bookings
     */
    public function index()
    {
        $bookingsTable = (new Booking())->getTable();
        $hasCancellationReason = Schema::hasColumn($bookingsTable, 'cancellation_reason');
        $now = now();

        $baseQuery = Booking::with(['hall', 'customer', 'user']);

        $upcomingQuery = clone $baseQuery;
        $upcomingQuery->where('start_datetime', '>=', $now);
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
        $completedQuery->where('end_datetime', '<', $now);
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

        $pendingActionBookings = Booking::with(['hall', 'customer', 'user'])
            ->where('admin_status', 'pending')
            ->where('booking_status', '!=', 'cancelled')
            ->latest()
            ->get();

        return view('admin.bookings.index', [
            'pendingActionBookings' => $pendingActionBookings,
            'upcomingBookings' => $upcomingQuery->latest('start_datetime')->get(),
            'completedBookings' => $completedQuery->latest('start_datetime')->get(),
            'cancelledBookings' => $cancelledQuery->latest('start_datetime')->get(),
        ]);
    }

    /**
     * Show create form
     */
    public function create(Request $request)
    {
        $halls = Hall::where('status', 'available')
            ->with('images')
            ->get();
        $customers = User::where('role', 'user')->get();

        $selectedHallId = $request->query('hall_id');
        $selectedHall = null;

        if ($selectedHallId && !$halls->pluck('id')->contains((int) $selectedHallId)) {
            $selectedHallId = null;
        }

        if ($selectedHallId) {
            $selectedHall = $halls->firstWhere('id', (int) $selectedHallId);
        }

        return view('admin.bookings.create', compact('halls', 'customers', 'selectedHallId', 'selectedHall'));
    }

    /**
     * Store booking
     */
    public function store(Request $request)
    {
        $request->validate([
            'hall_id' => 'required|exists:halls,id',
            'customer_id' => 'required|exists:users,id',
            'start_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_date' => 'required|date|after_or_equal:start_date',
            'end_time' => 'required|date_format:H:i',
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

        // Build full datetime from separate date + time fields
        $startDatetime = Carbon::parse($request->start_date . ' ' . $request->start_time);
        $endDatetime = Carbon::parse($request->end_date . ' ' . $request->end_time);

        // Validate end is after start
        if ($endDatetime->lte($startDatetime)) {
            return back()
                ->withErrors(['end_time' => 'End date/time must be after start date/time.'])
                ->withInput();
        }

        // Validate not in the past
        if ($startDatetime->lt(now())) {
            return back()
                ->withErrors(['start_date' => 'Booking cannot be in the past.'])
                ->withInput();
        }

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

        // 🔥 Availability check with 30-minute buffer using datetime range
        $availability = Booking::isSlotAvailable($request->hall_id, $startDatetime, $endDatetime);
        if (!$availability['available']) {
            return back()->with('error', $availability['message'])->withInput();
        }

        Booking::create([
            'hall_id' => $request->hall_id,
            'customer_id' => $request->customer_id,
            'created_by' => Auth::id(),
            'start_datetime' => $startDatetime,
            'end_datetime' => $endDatetime,
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
            'start_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_date' => 'required|date|after_or_equal:start_date',
            'end_time' => 'required|date_format:H:i',
            'booking_status' => 'nullable|in:pending,confirmed,cancelled,completed',
            'cancellation_reason' => 'nullable|string',
            'resources' => 'nullable|array',
            'resources.*' => 'in:projectors,sound_systems,lighting,seating,other',
            'resources_other' => 'nullable|string|max:500',
        ]);

        $startDatetime = Carbon::parse($request->start_date . ' ' . $request->start_time);
        $endDatetime = Carbon::parse($request->end_date . ' ' . $request->end_time);

        if ($endDatetime->lte($startDatetime)) {
            return back()
                ->withErrors(['end_time' => 'End date/time must be after start date/time.'])
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

        // 🔥 Check availability (excluding current booking)
        $availability = Booking::isSlotAvailable($request->hall_id, $startDatetime, $endDatetime, $booking->id);
        if (!$availability['available']) {
            return back()->with('error', $availability['message'])->withInput();
        }

        $booking->update([
            'hall_id' => $request->hall_id,
            'customer_id' => $request->customer_id,
            'start_datetime' => $startDatetime,
            'end_datetime' => $endDatetime,
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
            'admin_status' => 'required|in:approved,rejected,kept_pending',
        ]);

        $adminStatus = $request->admin_status;
        $bookingStatus = 'pending';
        $mediaStatus = $booking->media_status;

        if ($adminStatus === 'approved') {
            if ($booking->requiresMedia()) {
                $bookingStatus = 'waiting for media';
                $mediaStatus = 'pending';
            } else {
                $bookingStatus = 'confirmed';
            }
        } elseif ($adminStatus === 'rejected') {
            $bookingStatus = 'rejected';
        }

        $booking->update([
            'admin_status' => $adminStatus,
            'booking_status' => $bookingStatus,
            'media_status' => $mediaStatus,
        ]);

        // 1. Notify Staff
        $staff = $booking->customer ?: $booking->user;
        if ($staff) {
            $staff->notify(new BookingStatusUpdated($booking, 'admin', $adminStatus));
        }

        // 2. Notify Media Team (only if approved and media required)
        if ($adminStatus === 'approved' && $booking->requiresMedia()) {
            $mediaUsers = User::where('role', 'media')->get();
            foreach ($mediaUsers as $mediaUser) {
                $mediaUser->notify(new BookingStatusUpdated($booking, 'admin_approved_media', 'approved'));
            }
        }

        return back()->with('success', 'Booking status updated successfully.');
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
            ->latest('start_datetime')
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

        // 1. Notify Staff
        $staff = $booking->customer ?: $booking->user;
        if ($staff) {
            $staff->notify(new BookingStatusUpdated($booking, 'admin', 'cancelled'));
        }

        // 2. Notify Media Team (ONLY if it was already approved and media required)
        if ($booking->requiresMedia() && $booking->admin_status === 'approved') {
            $mediaUsers = User::where('role', 'media')->get();
            foreach ($mediaUsers as $mediaUser) {
                $mediaUser->notify(new BookingStatusUpdated($booking, 'admin_media', 'cancelled'));
            }
        }

        return redirect()
            ->route('admin.bookings.cancel.form')
            ->with('success', 'Booking cancellation details submitted successfully.');
    }

    /**
     * AJAX endpoint to check availability
     */
    public function checkAvailability(Request $request)
    {
        $hallId = $request->query('hall_id');
        $startDatetime = $request->query('start_datetime');
        $endDatetime = $request->query('end_datetime');
        $excludeId = $request->query('exclude_id');

        // Only fetch booked ranges when both dates are selected
        $bookedRanges = [];
        if ($hallId && $startDatetime && $endDatetime) {
            $query = Booking::where('hall_id', $hallId)
                ->where('booking_status', '!=', 'cancelled');

            // Only show bookings that overlap with the selected date range
            $rangeStart = Carbon::parse($startDatetime)->subDays(1);
            $rangeEnd = Carbon::parse($endDatetime)->addDays(1);
            $query->where('start_datetime', '<', $rangeEnd)
                  ->where('end_datetime', '>', $rangeStart);

            $bookedRanges = $query->get()
                ->map(function ($b) {
                    return [
                        'start' => Carbon::parse($b->start_datetime)->format('d M h:i A'),
                        'end' => Carbon::parse($b->end_datetime)->format('d M h:i A'),
                        'name' => $b->event_name
                    ];
                });
        }

        if (!$hallId || !$startDatetime || !$endDatetime) {
            return response()->json([
                'available' => true,
                'booked_ranges' => $bookedRanges
            ]);
        }

        $newStart = Carbon::parse($startDatetime);
        $newEnd = Carbon::parse($endDatetime);

        if ($newEnd->lte($newStart)) {
            return response()->json([
                'available' => false,
                'message' => 'End date/time must be after start date/time.',
                'booked_ranges' => $bookedRanges
            ]);
        }

        $availability = Booking::isSlotAvailable($hallId, $newStart, $newEnd, $excludeId);

        return response()->json([
            'available' => $availability['available'],
            'message' => $availability['message'],
            'booked_ranges' => $bookedRanges
        ]);
    }
}

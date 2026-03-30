<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Models\Waitlist;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Notifications\NewBookingRequest;
use App\Models\User;

class BookingController extends Controller
{
    /**
     * Show bookings created by logged-in user.
     */
    public function index()
    {
        $ownerColumn = $this->getBookingOwnerColumn();
        $baseQuery = Booking::with('hall');

        if ($ownerColumn) {
            $baseQuery->where($ownerColumn, Auth::id());
        } else {
            $baseQuery->whereRaw('1 = 0');
        }

        $now = now();
        $hasCancellationReason = $this->hasBookingColumn('cancellation_reason');

        $upcomingQuery = clone $baseQuery;
        $upcomingQuery->where('start_datetime', '>=', $now);
        if ($hasCancellationReason) {
            $upcomingQuery->where(function ($query) {
                $query->whereNull('cancellation_reason')
                    ->orWhere('cancellation_reason', '');
            });
        }

        $completedQuery = clone $baseQuery;
        $completedQuery->where('end_datetime', '<', $now);
        if ($hasCancellationReason) {
            $completedQuery->where(function ($query) {
                $query->whereNull('cancellation_reason')
                    ->orWhere('cancellation_reason', '');
            });
        }

        $cancelledQuery = clone $baseQuery;
        if ($hasCancellationReason) {
            $cancelledQuery->whereNotNull('cancellation_reason')
                ->where('cancellation_reason', '!=', '');
        } else {
            $cancelledQuery->whereRaw('1 = 0');
        }

        $upcomingBookings = $upcomingQuery->latest('start_datetime')->get();
        $completedBookings = $completedQuery->latest('start_datetime')->get();
        $cancelledBookings = $cancelledQuery->latest('start_datetime')->get();

        $waitlistedBookings = Waitlist::with('hall')
            ->where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'notified'])
            ->latest()
            ->get();

        return view('user.bookings.index', compact('upcomingBookings', 'completedBookings', 'cancelledBookings', 'waitlistedBookings'));
    }

    /**
     * Show user booking create form.
     */
    public function create(Request $request)
    {
        $halls = Hall::where('status', 'available')
            ->with('images')
            ->get();
        $selectedHallId = $request->query('hall_id');
        $selectedHall = null;

        if ($selectedHallId && !$halls->pluck('id')->contains((int) $selectedHallId)) {
            $selectedHallId = null;
        }

        if ($selectedHallId) {
            $selectedHall = $halls->firstWhere('id', (int) $selectedHallId);
        }

        return view('user.bookings.create', compact('halls', 'selectedHallId', 'selectedHall'));
    }

    /**
     * Show booking cancellation form for logged-in user.
     */
    public function showCancellationForm()
    {
        $ownerColumn = $this->getBookingOwnerColumn();
        $bookingsQuery = Booking::with('hall')->latest('start_datetime');

        if ($ownerColumn) {
            $bookingsQuery->where($ownerColumn, Auth::id());
        } else {
            $bookingsQuery->whereRaw('1 = 0');
        }

        if ($this->hasBookingColumn('cancellation_reason')) {
            $bookingsQuery->where(function ($query) {
                $query->whereNull('cancellation_reason')
                    ->orWhere('cancellation_reason', '');
            });
        }

        $bookings = $bookingsQuery->get();

        return view('user.bookings.cancel', compact('bookings'));
    }

    /**
     * Store user booking.
     */
    public function store(Request $request)
    {
        $request->validate([
            'hall_id' => 'required|exists:halls,id',
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

        // 🔥 Stricter availability check with 30-minute buffer using datetime range
        $availability = Booking::isSlotAvailable($request->hall_id, $startDatetime, $endDatetime);
        if (!$availability['available']) {
            return back()->with('error', $availability['message'])->withInput();
        }

        $ownerColumn = $this->getBookingOwnerColumn();
        if (!$ownerColumn) {
            return back()
                ->with('error', 'Booking owner column is missing in database. Please contact admin.')
                ->withInput();
        }

        $mediaReqs = $request->input('media_requirements', []);
        $bookingData = [
            'hall_id' => $request->hall_id,
            'start_datetime' => $startDatetime,
            'end_datetime' => $endDatetime,
            'booking_status' => 'pending',
            'admin_status' => 'pending',
            'media_status' => count($mediaReqs) > 0 ? 'pending' : 'not_required',
            'event_name' => $request->event_name,
            'event_department' => $request->event_department,
            'event_type' => $request->event_type,
            'coordinator_name' => $request->coordinator_name,
            'coordinator_phone' => $request->coordinator_phone,
            'coordinator_department' => $request->coordinator_department,
            'coordinator_email' => $request->coordinator_email,
            'coordinator_emergency_number' => $request->coordinator_emergency_number,
            'media_requirements' => $mediaReqs,
            'media_requirements_other' => $request->media_requirements_other,
            'resources' => $request->input('resources', []),
            'resources_other' => $request->resources_other,
        ];
        $bookingData[$ownerColumn] = Auth::id();
        if ($this->hasBookingColumn('created_by')) {
            $bookingData['created_by'] = Auth::id();
        }

        $booking = Booking::create($bookingData);

        // Notify Admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewBookingRequest($booking));
        }

        return redirect()->route('user.bookings.create')
            ->with('success', 'Booking request submitted successfully.');
    }

    /**
     * Submit cancellation reason for a user booking.
     */
    public function cancel(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
            'cancellation_reason_option' => 'required|in:postponded,event_cancelled,low_participation,other',
            'cancellation_reason_other' => 'nullable|string|max:500|required_if:cancellation_reason_option,other',
        ]);

        if (!$this->hasBookingColumn('cancellation_reason')) {
            return back()
                ->with('error', 'Cancellation reason field is not available yet. Please run latest migrations.')
                ->withInput();
        }

        $ownerColumn = $this->getBookingOwnerColumn();
        if (!$ownerColumn) {
            return back()->with('error', 'Unable to validate booking owner.')->withInput();
        }

        $booking = Booking::query()
            ->where('id', $validated['booking_id'])
            ->where($ownerColumn, Auth::id())
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

        // Make sure it disappears from Admin and Media queues
        $booking->update([
            'cancellation_reason' => $reasonText,
            'booking_status' => 'cancelled',
        ]);

        // Notify Admin that user cancelled
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\BookingStatusUpdated($booking, 'user', 'cancelled'));
        }

        // Notify Media if media was requested
        if ($booking->requiresMedia()) {
            $mediaUsers = \App\Models\User::where('role', 'media')->get();
            foreach ($mediaUsers as $mediaUser) {
                $mediaUser->notify(new \App\Notifications\BookingStatusUpdated($booking, 'user_media', 'cancelled'));
            }
        }

        // 🔥 Waitlist Logic: Find the first eligible waitlisted user
        \App\Models\Waitlist::notifyNextInWaitlist($booking->hall_id, $booking->start_datetime, $booking->end_datetime);

        return redirect()
            ->route('user.bookings.cancel.form')
            ->with('success', 'Booking cancellation details submitted successfully.');
    }

    /**
     * Join waitlist for a specific slot.
     */
    public function joinWaitlist(Request $request)
    {
        $request->validate([
            'hall_id' => 'required|exists:halls,id',
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
            'resources' => 'nullable|array',
        ]);

        $startDatetime = Carbon::parse($request->start_date . ' ' . $request->start_time);
        $endDatetime = Carbon::parse($request->end_date . ' ' . $request->end_time);

        if ($endDatetime->lte($startDatetime)) {
            return back()
                ->withErrors(['end_time' => 'End date/time must be after start date/time.'])
                ->withInput();
        }

        Waitlist::create([
            'hall_id' => $request->hall_id,
            'user_id' => Auth::id(),
            'start_datetime' => $startDatetime,
            'end_datetime' => $endDatetime,
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
            'status' => 'pending',
        ]);

        return redirect()->route('user.bookings.index')
            ->with('success', 'You have been added to the waitlist for this slot.');
    }

    /**
     * Confirm a waitlisted booking.
     */
    public function confirmWaitlist($waitlistId)
    {
        $waitlist = Waitlist::where('id', $waitlistId)
            ->where('user_id', Auth::id())
            ->where('status', 'notified')
            ->firstOrFail();

        if ($waitlist->expires_at->isPast()) {
            $waitlist->update(['status' => 'expired']);
            return redirect()->route('user.bookings.index')->with('error', 'The confirmation window has expired.');
        }

        DB::transaction(function () use ($waitlist) {
            $ownerColumn = $this->getBookingOwnerColumn();

            $bookingData = [
                'hall_id' => $waitlist->hall_id,
                'start_datetime' => $waitlist->start_datetime,
                'end_datetime' => $waitlist->end_datetime,
                'booking_status' => 'pending',
                'admin_status' => 'pending',
                'media_status' => !empty($waitlist->media_requirements) ? 'pending' : 'not_required',
                'event_name' => $waitlist->event_name,
                'event_department' => $waitlist->event_department,
                'event_type' => $waitlist->event_type,
                'coordinator_name' => $waitlist->coordinator_name,
                'coordinator_phone' => $waitlist->coordinator_phone,
                'coordinator_department' => $waitlist->coordinator_department,
                'coordinator_email' => $waitlist->coordinator_email,
                'coordinator_emergency_number' => $waitlist->coordinator_emergency_number,
                'media_requirements' => $waitlist->media_requirements,
                'media_requirements_other' => $waitlist->media_requirements_other,
                'resources' => $waitlist->resources,
                'resources_other' => $waitlist->resources_other,
            ];
            $bookingData[$ownerColumn] = Auth::id();
            if ($this->hasBookingColumn('created_by')) {
                $bookingData['created_by'] = Auth::id();
            }

            $booking = Booking::create($bookingData);
            $waitlist->update(['status' => 'confirmed']);

            // Notify Admins
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new NewBookingRequest($booking));
            }
        });

        return redirect()->route('user.bookings.index')
            ->with('success', 'Booking confirmed successfully.');
    }

    /**
     * AJAX endpoint to check availability
     */
    public function checkAvailability(Request $request)
    {
        $hallId = $request->query('hall_id');
        $startDatetime = $request->query('start_datetime');
        $endDatetime = $request->query('end_datetime');

        // Always fetch existing bookings for this hall to show in UI
        $bookedRanges = [];
        if ($hallId) {
            $query = Booking::where('hall_id', $hallId)
                ->where('booking_status', '!=', 'cancelled');

            // If we have a start/end datetime, only show potentially relevant bookings
            // (within a reasonable window)
            if ($startDatetime && $endDatetime) {
                $rangeStart = Carbon::parse($startDatetime)->subDays(1);
                $rangeEnd = Carbon::parse($endDatetime)->addDays(1);
                $query->where('start_datetime', '<', $rangeEnd)
                      ->where('end_datetime', '>', $rangeStart);
            } else {
                // Show upcoming bookings if no specific range given
                $query->where('end_datetime', '>', now());
            }

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

        // Validate end > start
        if ($newEnd->lte($newStart)) {
            return response()->json([
                'available' => false,
                'message' => 'End date/time must be after start date/time.',
                'booked_ranges' => $bookedRanges
            ]);
        }

        $availability = Booking::isSlotAvailable($hallId, $newStart, $newEnd);

        return response()->json([
            'available' => $availability['available'],
            'message' => $availability['message'],
            'booked_ranges' => $bookedRanges
        ]);
    }

    /**
     * Resolve booking owner column name for mixed schemas.
     */
    private function getBookingOwnerColumn(): ?string
    {
        $table = (new Booking())->getTable();

        if (Schema::hasColumn($table, 'customer_id')) {
            return 'customer_id';
        }

        if (Schema::hasColumn($table, 'user_id')) {
            return 'user_id';
        }

        return null;
    }

    /**
     * Check whether bookings table has a given column.
     */
    private function hasBookingColumn(string $column): bool
    {
        return Schema::hasColumn((new Booking())->getTable(), $column);
    }
}

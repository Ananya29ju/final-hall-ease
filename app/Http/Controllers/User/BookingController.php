<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

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

        $today = now()->toDateString();
        $hasCancellationReason = $this->hasBookingColumn('cancellation_reason');

        $upcomingQuery = clone $baseQuery;
        $upcomingQuery->whereDate('event_date', '>=', $today);
        if ($hasCancellationReason) {
            $upcomingQuery->where(function ($query) {
                $query->whereNull('cancellation_reason')
                    ->orWhere('cancellation_reason', '');
            });
        }

        $completedQuery = clone $baseQuery;
        $completedQuery->whereDate('event_date', '<', $today);
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

        $upcomingBookings = $upcomingQuery->latest('event_date')->latest('start_time')->get();
        $completedBookings = $completedQuery->latest('event_date')->latest('start_time')->get();
        $cancelledBookings = $cancelledQuery->latest('event_date')->latest('start_time')->get();

        return view('user.bookings.index', compact('upcomingBookings', 'completedBookings', 'cancelledBookings'));
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
        $bookingsQuery = Booking::with('hall')->latest('event_date');

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

        $exists = Booking::where('hall_id', $request->hall_id)
            ->where('event_date', $request->event_date)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time]);
            })
            ->exists();

        if ($exists) {
            return back()->with('error', 'Hall already booked for selected time.')->withInput();
        }

        $ownerColumn = $this->getBookingOwnerColumn();
        if (!$ownerColumn) {
            return back()
                ->with('error', 'Booking owner column is missing in database. Please contact admin.')
                ->withInput();
        }

        $bookingData = [
            'hall_id' => $request->hall_id,
            'event_date' => $request->event_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'booking_status' => 'pending',
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
        ];
        $bookingData[$ownerColumn] = Auth::id();
        if ($this->hasBookingColumn('created_by')) {
            $bookingData['created_by'] = Auth::id();
        }

        Booking::create($bookingData);

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

        $booking->update([
            'cancellation_reason' => $reasonText,
        ]);

        return redirect()
            ->route('user.bookings.cancel.form')
            ->with('success', 'Booking cancellation details submitted successfully.');
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

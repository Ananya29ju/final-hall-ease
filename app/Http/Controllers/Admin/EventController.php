<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class EventController extends Controller
{
    /**
     * Display a listing of events (bookings with event details)
     */
    public function index()
    {
        $eventsQuery = Booking::with(['user', 'customer', 'hall']);

        if (Schema::hasColumn((new Booking())->getTable(), 'booking_status')) {
            $eventsQuery->whereIn('booking_status', ['pending', 'confirmed', 'completed']);
        }

        $events = $eventsQuery
            ->latest('event_date')
            ->latest('start_time')
            ->paginate(10);
        return view('admin.events.index', compact('events'));
    }

    /**
     * Display the specified event
     */
    public function show(Booking $event)
    {
        $event->load(['user', 'customer', 'hall']);
        return view('admin.events.show', compact('event'));
    }

    /**
     * Update event details
     */
    public function update(Request $request, Booking $event)
    {
        $validated = $request->validate([
            'event_date' => 'date',
            'start_time' => 'date_format:H:i:s',
            'end_time' => 'date_format:H:i:s',
            'booking_status' => 'in:pending,confirmed,cancelled',
        ]);

        $event->update($validated);

        return redirect()
            ->back()
            ->with('success', 'Event updated successfully!');
    }
}

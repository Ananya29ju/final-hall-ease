@extends('layouts/contentNavbarLayout')

@section('title', 'Media Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-4">
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="text-muted d-block mb-1">New Hall Bookings</span>
                            <h3 class="card-title mb-2">{{ $newBookingCount }}</h3>
                            <small class="text-muted">Bookings waiting for media attention.</small>
                        </div>
                        <span class="badge bg-label-warning p-2">
                            <i class="bx bx-camera fs-4"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">Latest Booking Requests</h5>
                        <small class="text-muted">Media team gets notified whenever a hall is booked.</small>
                    </div>
                    <a href="{{ route('media.notifications.index') }}" class="btn btn-sm btn-outline-primary">View All Notifications</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Booking</th>
                                <th>User</th>
                                <th>Hall</th>
                                <th>Event</th>
                                <th>Date / Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($newBookings as $booking)
                                <tr>
                                    <td>#{{ $booking->id }}</td>
                                    <td>{{ $booking->customer->name ?? $booking->user->name ?? 'N/A' }}</td>
                                    <td>{{ $booking->hall->name ?? 'N/A' }}</td>
                                    <td>{{ $booking->event_name ?? 'N/A' }}</td>
                                    <td>{{ $booking->formatted_datetime_range }}</td>
                                    <td>
                                        <a href="{{ route('media.bookings.index') }}" class="btn btn-sm btn-label-primary">
                                            <i class="bx bx-show-alt me-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No hall bookings available right now.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

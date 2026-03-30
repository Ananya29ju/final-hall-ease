@extends('layouts/contentNavbarLayout')

@section('title', 'Notifications')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">New Booking Notifications</h5>
                    <span class="badge bg-primary">{{ $newBookings->count() }}</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Booking</th>
                                    <th>User</th>
                                    <th>Hall</th>
                                    <th>Event</th>
                                    <th>Date / Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($newBookings as $booking)
                                    <tr>
                                        <td>#{{ $booking->id }}</td>
                                        <td>{{ $booking->customer->name ?? $booking->user->name ?? 'N/A' }}</td>
                                        <td>{{ $booking->hall->name ?? 'N/A' }}</td>
                                        <td>{{ $booking->event_name ?? 'N/A' }}</td>
                                        <td>{{ $booking->formatted_datetime_range }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No new bookings found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Cancellation Notifications</h5>
                    <span class="badge bg-danger">{{ $cancellationNotifications->count() }}</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Booking</th>
                                    <th>User</th>
                                    <th>Hall</th>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th>Cancellation Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cancellationNotifications as $booking)
                                    <tr>
                                        <td>#{{ $booking->id }}</td>
                                        <td>{{ $booking->customer->name ?? $booking->user->name ?? 'N/A' }}</td>
                                        <td>{{ $booking->hall->name ?? 'N/A' }}</td>
                                        <td>{{ $booking->event_name ?? 'N/A' }}</td>
                                        <td>{{ $booking->formatted_datetime_range }}</td>
                                        <td>{{ $booking->cancellation_reason }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No cancellation notifications found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

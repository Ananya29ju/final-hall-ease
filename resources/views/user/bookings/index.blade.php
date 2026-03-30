@extends('layouts/contentNavbarLayout')

@section('title', 'My Bookings')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">My Hall Bookings</h5>
        <a href="{{ route('user.bookings.create') }}" class="btn btn-primary">
            <i class="bx bx-plus"></i> New Booking
        </a>
    </div>

    @php
        $renderRows = function ($bookings, $showCancellationReason = false) {
            $statusBadges = [
                'pending' => 'warning',
                'approved' => 'success',
                'accepted' => 'success',
                'rejected' => 'danger',
                'kept_pending' => 'info',
                'not_required' => 'secondary',
                'waiting for media' => 'info',
                'confirmed' => 'success',
                'confirmed without media' => 'success',
            ];

            foreach ($bookings as $booking) {
                $adminBadge = $statusBadges[$booking->admin_status] ?? 'secondary';
                $mediaBadge = $statusBadges[$booking->media_status] ?? 'secondary';
                $bookingBadge = $statusBadges[$booking->booking_status] ?? 'secondary';

                echo '<tr>';
                echo '<td>' . e($booking->id) . '</td>';
                echo '<td>' . e(optional($booking->hall)->name ?? 'N/A') . '</td>';
                echo '<td>' . e($booking->event_name ?? 'N/A') . '</td>';
                echo '<td>' . e($booking->formatted_datetime_range) . '</td>';

                echo '<td>';
                echo '<div class="d-flex flex-column gap-1">';
                echo '<span class="badge bg-' . $bookingBadge . '">Overall: ' . e(ucfirst(str_replace('_', ' ', $booking->booking_status))) . '</span>';
                echo '<div class="d-flex gap-1">';
                echo '<small class="badge bg-label-' . $adminBadge . '" style="font-size: 0.65rem;">Admin: ' . e(ucfirst(str_replace('_', ' ', $booking->admin_status))) . '</small>';
                if ($booking->media_status !== 'not_required') {
                    echo '<small class="badge bg-label-' . $mediaBadge . '" style="font-size: 0.65rem;">Media: ' . e(ucfirst(str_replace('_', ' ', $booking->media_status))) . '</small>';
                }
                echo '</div>';
                echo '</div>';
                echo '</td>';

                if ($showCancellationReason) {
                    echo '<td>' . e($booking->cancellation_reason ?? '-') . '</td>';
                }
                echo '<td>' . e(optional($booking->created_at)->format('M d, Y h:i A')) . '</td>';
                echo '</tr>';
            }
        };
    @endphp

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Upcoming Bookings</h6>
            <span class="badge bg-success">{{ $upcomingBookings->count() }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Hall</th>
                            <th>Event Name</th>
                            <th>Date / Time</th>
                            <th>Status</th>
                            <th>Created On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($upcomingBookings->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center text-muted">No upcoming bookings.</td>
                            </tr>
                        @else
                            {!! $renderRows($upcomingBookings) !!}
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Completed (Done) Bookings</h6>
            <span class="badge bg-secondary">{{ $completedBookings->count() }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Hall</th>
                            <th>Event Name</th>
                            <th>Date / Time</th>
                            <th>Status</th>
                            <th>Created On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($completedBookings->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center text-muted">No completed bookings.</td>
                            </tr>
                        @else
                            {!! $renderRows($completedBookings) !!}
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Cancelled Bookings</h6>
            <span class="badge bg-danger">{{ $cancelledBookings->count() }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Hall</th>
                            <th>Event Name</th>
                            <th>Date / Time</th>
                            <th>Status</th>
                            <th>Cancellation Reason</th>
                            <th>Created On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($cancelledBookings->isEmpty())
                            <tr>
                                <td colspan="7" class="text-center text-muted">No cancelled bookings.</td>
                            </tr>
                        @else
                            {!! $renderRows($cancelledBookings, true) !!}
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

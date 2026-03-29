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
            $resourceLabels = [
                'projectors' => 'Projectors',
                'sound_systems' => 'Sound Systems',
                'lighting' => 'Lighting',
                'seating' => 'Seating',
                'other' => 'Other',
            ];

            foreach ($bookings as $booking) {
                $resources = collect($booking->resources ?? [])
                    ->map(fn ($value) => $resourceLabels[$value] ?? ucfirst(str_replace('_', ' ', $value)))
                    ->values()
                    ->all();
                if (!empty($booking->resources_other)) {
                    $resources[] = 'Other: ' . $booking->resources_other;
                }
                $resourcesText = count($resources) ? implode(', ', $resources) : '-';

                echo '<tr>';
                echo '<td>' . e($booking->id) . '</td>';
                echo '<td>' . e(optional($booking->hall)->name ?? 'N/A') . '</td>';
                echo '<td>' . e($booking->event_name ?? 'N/A') . '</td>';
                echo '<td>' . e(optional($booking->event_date)->format('M d, Y')) . '</td>';
                echo '<td>' . e(\Carbon\Carbon::parse($booking->start_time)->format('H:i')) . ' - ' . e(\Carbon\Carbon::parse($booking->end_time)->format('H:i')) . '</td>';
                echo '<td>' . e(ucfirst($booking->booking_status ?? 'pending')) . '</td>';
                echo '<td>' . e($resourcesText) . '</td>';
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
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Resources</th>
                            <th>Created On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($upcomingBookings->isEmpty())
                            <tr>
                                <td colspan="8" class="text-center text-muted">No upcoming bookings.</td>
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
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Resources</th>
                            <th>Created On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($completedBookings->isEmpty())
                            <tr>
                                <td colspan="8" class="text-center text-muted">No completed bookings.</td>
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
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Resources</th>
                            <th>Cancellation Reason</th>
                            <th>Created On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($cancelledBookings->isEmpty())
                            <tr>
                                <td colspan="9" class="text-center text-muted">No cancelled bookings.</td>
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

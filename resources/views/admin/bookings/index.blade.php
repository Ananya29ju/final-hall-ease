@extends('layouts/contentNavbarLayout')

@section('title', 'Bookings')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Bookings</h4>
        <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary">
            <i class="bx bx-plus"></i> Add Booking
        </a>
    </div>

    @php
        $renderRows = function ($bookings, $isCancelled = false) {
            $resourceLabels = [
                'projectors' => 'Projectors',
                'sound_systems' => 'Sound Systems',
                'lighting' => 'Lighting',
                'seating' => 'Seating',
                'other' => 'Other',
            ];

            $statusBadge = function ($status) {
                return match ($status) {
                    'confirmed', 'confirmed without media' => 'success',
                    'completed' => 'secondary',
                    'cancelled', 'rejected' => 'danger',
                    'waiting for media' => 'info',
                    default => 'warning',
                };
            };

            foreach ($bookings as $booking) {
                $overallBadge = $statusBadge($booking->booking_status);
                $adminBadge = $booking->admin_status === 'approved' ? 'success' : ($booking->admin_status === 'rejected' ? 'danger' : 'warning');
                $mediaBadge = $booking->media_status === 'accepted' ? 'success' : ($booking->media_status === 'rejected' ? 'danger' : 'warning');

                echo '<tr>';
                echo '<td>' . e($booking->id) . '</td>';
                echo '<td>' . e(optional($booking->hall)->name ?? 'N/A') . '</td>';
                echo '<td>' . e(optional($booking->customer)->name ?? optional($booking->user)->name ?? 'N/A') . '</td>';
                echo '<td>' . e($booking->formatted_datetime_range) . '</td>';
                echo '<td>';
                echo '<div class="d-flex flex-column gap-1">';
                echo '<span class="badge bg-' . $overallBadge . '">Overall: ' . e(ucfirst(str_replace('_', ' ', $booking->booking_status))) . '</span>';
                echo '<div class="d-flex gap-1">';
                echo '<small class="badge bg-label-' . $adminBadge . '" style="font-size: 0.65rem;">Admin: ' . e(ucfirst(str_replace('_', ' ', $booking->admin_status))) . '</small>';
                if ($booking->media_status !== 'not_required') {
                    echo '<small class="badge bg-label-' . $mediaBadge . '" style="font-size: 0.65rem;">Media: ' . e(ucfirst(str_replace('_', ' ', $booking->media_status))) . '</small>';
                }
                echo '</div>';
                echo '</div>';
                echo '</td>';

                echo '<td>';
                echo '<div class="d-flex flex-wrap gap-1">';
                // Approval Buttons
                if ($booking->admin_status === 'pending' || $booking->admin_status === 'kept_pending') {
                    echo '<form action="' . e(route('admin.bookings.updateStatus', $booking->id)) . '" method="POST" class="d-inline">';
                    echo csrf_field(); echo method_field('PATCH');
                    echo '<input type="hidden" name="admin_status" value="approved">';
                    echo '<button class="btn btn-sm btn-success" title="Approve"><i class="bx bx-check"></i></button>';
                    echo '</form>';

                    echo '<form action="' . e(route('admin.bookings.updateStatus', $booking->id)) . '" method="POST" class="d-inline">';
                    echo csrf_field(); echo method_field('PATCH');
                    echo '<input type="hidden" name="admin_status" value="rejected">';
                    echo '<button class="btn btn-sm btn-danger" title="Reject" onclick="return confirm(\'Reject this booking?\')"><i class="bx bx-x"></i></button>';
                    echo '</form>';

                    if ($booking->admin_status === 'pending') {
                        echo '<form action="' . e(route('admin.bookings.updateStatus', $booking->id)) . '" method="POST" class="d-inline">';
                        echo csrf_field(); echo method_field('PATCH');
                        echo '<input type="hidden" name="admin_status" value="kept_pending">';
                        echo '<button class="btn btn-sm btn-info" title="Keep Pending"><i class="bx bx-time"></i></button>';
                        echo '</form>';
                    }
                }
                
                echo '<a href="' . e(route('admin.bookings.show', $booking->id)) . '" class="btn btn-sm btn-outline-info" title="View"><i class="bx bx-show"></i></a>';
                echo '<form action="' . e(route('admin.bookings.destroy', $booking->id)) . '" method="POST" class="d-inline">';
                echo csrf_field(); echo method_field('DELETE');
                echo '<button class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Delete this booking?\')"><i class="bx bx-trash"></i></button>';
                echo '</form>';
                echo '</div>';
                echo '</td>';
                echo '</tr>';
            }
        };
    @endphp

    <div class="card mb-4 border-primary">
        <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
            <h6 class="mb-0 text-white">Pending Action Bookings</h6>
            <span class="badge bg-white text-primary">{{ $pendingActionBookings->count() }}</span>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Hall</th>
                            <th>Staff</th>
                            <th>Date / Time</th>
                            <th>Status Details</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($pendingActionBookings->isEmpty())
                            <tr>
                                <td colspan="7" class="text-center text-muted">No bookings pending your action.</td>
                            </tr>
                        @else
                            {!! $renderRows($pendingActionBookings) !!}
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Upcoming Approved Bookings</h6>
            <span class="badge bg-success">{{ $upcomingBookings->count() }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Hall</th>
                            <th>Staff</th>
                            <th>Date / Time</th>
                            <th>Status Details</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($upcomingBookings->isEmpty())
                            <tr>
                                <td colspan="7" class="text-center text-muted text-center text-muted">No upcoming bookings.</td>
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
                            <th>Staff</th>
                            <th>Date / Time</th>
                            <th>Status</th>
                            <th>Resources</th>
                            <th>Actions</th>
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
                            <th>Staff</th>
                            <th>Date / Time</th>
                            <th>Status</th>
                            <th>Resources</th>
                            <th>Cancellation Reason</th>
                            <th>Actions</th>
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

<script>
    // Simple auto-refresh every 30 seconds to simulate "real-time" sync with Media updates
    setTimeout(function() {
        location.reload();
    }, 30000);
</script>
@endsection

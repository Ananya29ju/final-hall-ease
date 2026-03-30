@extends('layouts/contentNavbarLayout')

@section('title', 'Cancel Booking')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Booking Cancellation</h4>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($bookings->isEmpty())
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-1"></i> No bookings found.
                </div>
            @else
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    @foreach($bookings as $booking)
                        <div class="col">
                            <div class="card h-100 border shadow-none {{ $booking->booking_status === 'cancelled' ? 'bg-label-secondary border-secondary' : 'border-primary' }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title mb-0 text-truncate" title="{{ $booking->event_name }}">{{ $booking->event_name ?: 'Unnamed Event' }}</h5>
                                        @php
                                            $statusBadge = match ($booking->booking_status) {
                                                'confirmed', 'confirmed without media' => 'success',
                                                'completed' => 'secondary',
                                                'cancelled', 'rejected' => 'danger',
                                                'waiting for media', 'pending' => 'warning',
                                                default => 'info',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusBadge }}">{{ ucfirst($booking->booking_status) }}</span>
                                    </div>
                                    <hr class="mt-0 mb-3">
                                    <ul class="list-unstyled mb-3 text-muted">
                                        <li class="mb-2 d-flex align-items-center"><i class="bx bx-buildings text-primary fs-5 me-2"></i> {{ $booking->hall->name ?? 'Unknown Hall' }}</li>
                                        <li class="mb-2 d-flex align-items-center"><i class="bx bx-calendar-event text-success fs-5 me-2"></i> {{ $booking->formatted_datetime_range }}</li>
                                    </ul>
                                </div>
                                <div class="card-footer bg-transparent border-top mt-auto pt-3">
                                    @php
                                        // Disable cancel if it's already cancelled, completed, or if the event is strictly in the past
                                        $isPast = $booking->end_datetime && $booking->end_datetime->isPast();
                                        $isDisabled = in_array($booking->booking_status, ['cancelled', 'completed', 'rejected']) || $isPast;
                                    @endphp
                                    <button 
                                        type="button" 
                                        class="btn w-100 {{ $isDisabled ? 'btn-outline-secondary' : 'btn-danger' }}" 
                                        {{ $isDisabled ? 'disabled' : '' }}
                                        onclick="openCancelModal({{ $booking->id }}, '{{ addslashes($booking->event_name) }}', '{{ $booking->hall->name ?? 'Hall' }}')"
                                    >
                                        <i class="bx bx-x-circle me-1"></i> {{ $isDisabled ? 'Cannot Cancel' : 'Cancel Booking' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Cancellation Modal -->
<div class="modal fade" id="cancelBookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-danger">
            <form action="{{ route('user.bookings.cancel.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="booking_id" id="modal_booking_id">

                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="cancelModalTitle"><i class="bx bx-error-circle me-1"></i> Confirm Cancellation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> You are about to cancel the booking for <strong id="modal_event_name">...</strong> at <strong id="modal_hall_name">...</strong>. This action will immediately free the time slot.
                    </div>

                    <h6 class="mb-3 mt-4">Please provide a reason for cancellation:</h6>
                    
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="cancellation_reason_option" id="reason_postponded" value="postponded" required>
                        <label class="form-check-label" for="reason_postponded">Postponed</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="cancellation_reason_option" id="reason_event_cancelled" value="event_cancelled" required>
                        <label class="form-check-label" for="reason_event_cancelled">Event Cancelled</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="cancellation_reason_option" id="reason_low_participation" value="low_participation" required>
                        <label class="form-check-label" for="reason_low_participation">Low Participation</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="cancellation_reason_option" id="reason_other" value="other" required>
                        <label class="form-check-label" for="reason_other">Other</label>
                    </div>

                    <label for="cancellation_reason_other" class="form-label mt-2">Other Reason (if selected)</label>
                    <textarea id="cancellation_reason_other" name="cancellation_reason_other" rows="3" class="form-control" placeholder="Enter your detailed reason here"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openCancelModal(bookingId, eventName, hallName) {
        document.getElementById('modal_booking_id').value = bookingId;
        document.getElementById('modal_event_name').textContent = eventName;
        document.getElementById('modal_hall_name').textContent = hallName;
        
        var myModal = new bootstrap.Modal(document.getElementById('cancelBookingModal'));
        myModal.show();
    }
</script>
@endsection

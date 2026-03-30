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

            <form action="{{ route('admin.bookings.cancel.submit') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <h5 class="mb-3">Select Existing Booking</h5>
                    <select name="booking_id" class="form-select" required>
                        <option value="">Choose Booking</option>
                        @foreach($bookings as $booking)
                            <option value="{{ $booking->id }}" {{ (string) old('booking_id') === (string) $booking->id ? 'selected' : '' }}>
                                #{{ $booking->id }} -
                                {{ $booking->hall->name ?? 'Hall' }} -
                                {{ optional($booking->customer)->name ?? optional($booking->user)->name ?? 'N/A' }} -
                                {{ $booking->formatted_datetime_range }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <h5 class="mb-3">Cancellation Details</h5>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="cancellation_reason_option" id="reason_postponded" value="postponded" {{ old('cancellation_reason_option') === 'postponded' ? 'checked' : '' }} required>
                        <label class="form-check-label" for="reason_postponded">Postponded</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="cancellation_reason_option" id="reason_event_cancelled" value="event_cancelled" {{ old('cancellation_reason_option') === 'event_cancelled' ? 'checked' : '' }} required>
                        <label class="form-check-label" for="reason_event_cancelled">Event Cancelled</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="cancellation_reason_option" id="reason_low_participation" value="low_participation" {{ old('cancellation_reason_option') === 'low_participation' ? 'checked' : '' }} required>
                        <label class="form-check-label" for="reason_low_participation">Low Participation</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="cancellation_reason_option" id="reason_other" value="other" {{ old('cancellation_reason_option') === 'other' ? 'checked' : '' }} required>
                        <label class="form-check-label" for="reason_other">Other</label>
                    </div>

                    <label for="cancellation_reason_other" class="form-label">Other Reason (if selected)</label>
                    <textarea id="cancellation_reason_other" name="cancellation_reason_other" rows="3" class="form-control" placeholder="Enter your reason">{{ old('cancellation_reason_other') }}</textarea>
                </div>

                <div>
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-x-circle"></i> Submit Cancellation
                    </button>
                    <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

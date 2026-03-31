@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Booking')

@section('page-style')
<style>
    .datetime-range-group {
        border: 1px solid #e8edf4;
        border-radius: 0.75rem;
        padding: 1rem;
        background: #f9fbff;
        position: relative;
    }

    .datetime-range-group .range-label {
        position: absolute;
        top: -0.65rem;
        left: 1rem;
        background: #f9fbff;
        padding: 0 0.5rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #696cff;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .datetime-range-arrow {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #696cff;
        padding: 0.5rem 0;
    }
</style>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h4>Edit Booking</h4>
    </div>

    <div class="card-body">

        {{-- Success --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Errors --}}
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">

                {{-- Hall --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Select Hall</label>
                    <select name="hall_id" class="form-select" required>
                        @foreach($halls as $hall)
                            <option value="{{ $hall->id }}"
                                {{ $booking->hall_id == $hall->id ? 'selected' : '' }}>
                                {{ $hall->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Staff --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Select Staff</label>
                    <select name="customer_id" class="form-select" required>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}"
                                {{ $booking->customer_id == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            {{-- Start Date/Time → End Date/Time --}}
            <div class="row">
                <div class="col-md-5">
                    <div class="datetime-range-group mb-3">
                        <span class="range-label"><i class="bx bx-log-in-circle me-1"></i> Start</span>
                        <div class="row mt-2">
                            <div class="col-sm-6 mb-2 mb-sm-0">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date"
                                       value="{{ old('start_date', optional($booking->start_datetime)->format('Y-m-d')) }}"
                                       class="form-control" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Start Time</label>
                                <input type="time" name="start_time"
                                       value="{{ old('start_time', optional($booking->start_datetime)->format('H:i')) }}"
                                       class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-2 datetime-range-arrow">
                    <i class="bx bx-right-arrow-alt"></i>
                </div>

                <div class="col-md-5">
                    <div class="datetime-range-group mb-3">
                        <span class="range-label"><i class="bx bx-log-out-circle me-1"></i> End</span>
                        <div class="row mt-2">
                            <div class="col-sm-6 mb-2 mb-sm-0">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date"
                                       value="{{ old('end_date', optional($booking->end_datetime)->format('Y-m-d')) }}"
                                       class="form-control" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">End Time</label>
                                <input type="time" name="end_time"
                                       value="{{ old('end_time', optional($booking->end_datetime)->format('H:i')) }}"
                                       class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cancellation Reason --}}
            <div class="col-12 mb-3">
                <label class="form-label">Cancellation Reason (If Cancelled)</label>
                <textarea name="cancellation_reason"
                          class="form-control"
                          rows="3">{{ $booking->cancellation_reason }}</textarea>
            </div>

            <div class="col-12">
                <hr class="my-4">
                <h5 class="mb-3">Resources Required</h5>
            </div>
            @php
                $selectedResources = old('resources', $booking->resources ?? []);
            @endphp
            <div class="col-md-12 mb-3">
                <div class="d-flex flex-wrap gap-3">
                    @foreach([
                        'projector' => 'Projector',
                        'mics' => 'Mics',
                        'chairs' => 'Chairs',
                        'tables' => 'Tables',
                        'sapling' => 'Sapling',
                        'glass_and_water' => 'Glass and Water',
                        'other' => 'others'
                    ] as $value => $label)
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="edit_resource_{{ $value }}"
                                   name="resources[]"
                                   value="{{ $value }}"
                                   {{ in_array($value, $selectedResources, true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="edit_resource_{{ $value }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-md-12 mb-3 {{ in_array('other', $selectedResources, true) ? '' : 'd-none' }}" id="resources_other_container">
                <label class="form-label">Other Resource (Please specify)</label>
                <input type="text"
                       name="resources_other"
                       value="{{ old('resources_other', $booking->resources_other) }}"
                       class="form-control"
                       placeholder="Enter other resource requirement">
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save"></i> Update Booking
                </button>

                <a href="{{ route('admin.bookings.index') }}"
                   class="btn btn-secondary">
                    Cancel
                </a>
            </div>

        </form>

    </div>
</div>

@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle "Other" input visibility for Resources Required
    const resourcesOtherCheckbox = document.getElementById('edit_resource_other');
    const resourcesOtherContainer = document.getElementById('resources_other_container');

    if (resourcesOtherCheckbox && resourcesOtherContainer) {
        resourcesOtherCheckbox.addEventListener('change', function() {
            if (this.checked) {
                resourcesOtherContainer.classList.remove('d-none');
            } else {
                resourcesOtherContainer.classList.add('d-none');
                resourcesOtherContainer.querySelector('input').value = '';
            }
        });
    }
});
</script>
@endsection

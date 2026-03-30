@extends('layouts/contentNavbarLayout')

@section('title', 'Create Booking')

@section('page-style')
<style>
    .selected-hall-hero {
        border: 1px solid #e8edf4;
        border-radius: 0.9rem;
    }

    .selected-hall-hero .hall-photo {
        width: 100%;
        height: 280px;
        object-fit: cover;
        border-radius: 0.75rem 0.75rem 0 0;
    }

    .selected-hall-hero .hall-photo-single {
        width: 100%;
        height: 280px;
        object-fit: cover;
        border-radius: 0.75rem 0.75rem 0 0;
    }

    .selected-hall-hero .hall-info-title {
        font-weight: 800;
        color: #2f3349;
        font-size: 1.75rem;
        text-align: left;
        width: 100%;
    }

    .selected-hall-hero .hall-overview-head {
        text-align: left;
    }

    .selected-hall-hero .hall-meta-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
        margin-top: 0.85rem;
    }

    .selected-hall-hero .meta-item {
        border: 1px solid #edf1f7;
        background: #f9fbff;
        border-radius: 0.6rem;
        padding: 0.65rem 0.75rem;
    }

    .selected-hall-hero .meta-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #7a8797;
        margin-bottom: 0.15rem;
    }

    .selected-hall-hero .meta-value {
        font-weight: 600;
        color: #2f3349;
        line-height: 1.2;
    }

    .selected-hall-hero .hall-desc {
        margin-top: 0.85rem;
        padding: 0.75rem;
        border-radius: 0.6rem;
        background: #f7f9fc;
        border: 1px solid #edf1f7;
        color: #5d6674;
    }

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

    @media (max-width: 768px) {
        .selected-hall-hero .hall-photo,
        .selected-hall-hero .hall-photo-single {
            height: 220px;
        }

        .selected-hall-hero .hall-meta-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h4>Create Booking</h4>
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

        @if(!empty($selectedHall))
            @php
                $hallImageUrls = $selectedHall->images
                    ->pluck('image_path')
                    ->filter()
                    ->map(fn ($path) => asset('storage/' . ltrim($path, '/')))
                    ->values()
                    ->all();

                if (empty($hallImageUrls) && !empty($selectedHall->image)) {
                    $legacyPath = ltrim($selectedHall->image, '/');
                    $hallImageUrls[] = str_starts_with($legacyPath, 'halls/')
                        ? asset('storage/' . $legacyPath)
                        : asset($legacyPath);
                }
            @endphp

            <div class="card mb-4 selected-hall-hero">
                <div class="card-header">
                    <div class="hall-overview-head">
                        <h5 class="hall-info-title mb-1">{{ $selectedHall->name }}</h5>
                        <span class="badge bg-info text-dark">{{ count($hallImageUrls) }} Photo(s)</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-6">
                            @if (count($hallImageUrls) > 1)
                                <div id="selectedHallCarousel" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        @foreach ($hallImageUrls as $index => $imageUrl)
                                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                                <img src="{{ $imageUrl }}" class="hall-photo" alt="{{ $selectedHall->name }} image {{ $index + 1 }}">
                                            </div>
                                        @endforeach
                                    </div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#selectedHallCarousel" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#selectedHallCarousel" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                </div>
                            @elseif (count($hallImageUrls) === 1)
                                <img src="{{ $hallImageUrls[0] }}" class="hall-photo-single" alt="{{ $selectedHall->name }}">
                            @else
                                <div class="alert alert-light border mb-0">No hall images uploaded yet.</div>
                            @endif
                        </div>

                        <div class="col-lg-6">
                            <div class="d-flex justify-content-start mb-2">
                                <span class="badge bg-{{ $selectedHall->status === 'available' ? 'success' : 'warning' }}">
                                    Status: {{ ucfirst($selectedHall->status) }}
                                </span>
                            </div>

                            <div class="hall-meta-grid">
                                <div class="meta-item">
                                    <div class="meta-label">Campus</div>
                                    <div class="meta-value">{{ $selectedHall->campus_name ?? 'N/A' }}</div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-label">Block</div>
                                    <div class="meta-value">{{ $selectedHall->location }}</div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-label">Capacity</div>
                                    <div class="meta-value">{{ $selectedHall->capacity }} persons</div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-label">Hall ID</div>
                                    <div class="meta-value">#{{ $selectedHall->id }}</div>
                                </div>
                            </div>

                            @if(!empty($selectedHall->description))
                                <div class="hall-desc">
                                    <strong>Description:</strong> {{ $selectedHall->description }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.bookings.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Select Hall</label>
                    <select name="hall_id" id="hall_id" class="form-select" required>
                        <option value="">Choose Hall</option>
                        @foreach($halls as $hall)
                            <option value="{{ $hall->id }}" {{ (string) old('hall_id', $selectedHallId ?? '') === (string) $hall->id ? 'selected' : '' }}>
                                {{ $hall->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Select Staff</label>
                    <select name="customer_id" id="customer_id" class="form-select" required>
                        <option value="">Choose Staff</option>
                        @foreach($customers as $customer)
                            @php
                                $phoneDisplay = $customer->phone ? '(' . $customer->phone . ')' : '';
                            @endphp
                            <option value="{{ $customer->id }}" {{ (string) old('customer_id') === (string) $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }} {{ $phoneDisplay }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @php
                // Generate time options with 30-min intervals from 7:00 AM to 10:00 PM
                $timeOptions = [];
                for ($hour = 7; $hour <= 22; $hour++) {
                    foreach ([0, 30] as $minute) {
                        $timeValue = sprintf('%02d:%02d', $hour, $minute);
                        $displayTime = date('h:i A', strtotime($timeValue));
                        $timeOptions[$timeValue] = $displayTime;
                    }
                }
            @endphp

            {{-- Start Date/Time → End Date/Time --}}
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bx bx-calendar-check me-2"></i>Booking Schedule</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="row g-4 align-items-stretch">
                        {{-- Start Section --}}
                        <div class="col-md-5">
                            <div class="h-100 p-3 rounded bg-light border d-flex flex-column">
                                <h6 class="text-primary mb-3"><i class="bx bx-log-in-circle me-1"></i> Start</h6>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted mb-1">Date</label>
                                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" class="form-control" required>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="form-label fw-semibold small text-muted mb-1">Time</label>
                                    <select name="start_time" id="start_time" class="form-select" required>
                                        <option value="">Select Time</option>
                                        @foreach($timeOptions as $value => $label)
                                            @php
                                                $oldStartTime = old('start_time');
                                                if ($oldStartTime && strlen($oldStartTime) > 5) {
                                                    $oldStartTime = substr($oldStartTime, 0, 5);
                                                }
                                            @endphp
                                            <option value="{{ $value }}" {{ $oldStartTime == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Arrow & Duration Section --}}
                        <div class="col-md-2 d-flex align-items-center justify-content-center">
                            <div class="text-center">
                                <i class="bx bx-right-arrow-alt fs-1 text-primary d-block mb-2"></i>
                                <div id="duration-display" class="badge bg-info text-dark px-3 py-2 d-none">
                                    <i class="bx bx-time me-1"></i>
                                    <span id="duration-text" class="fw-semibold">--</span>
                                </div>
                            </div>
                        </div>

                        {{-- End Section --}}
                        <div class="col-md-5">
                            <div class="h-100 p-3 rounded bg-light border d-flex flex-column">
                                <h6 class="text-primary mb-3"><i class="bx bx-log-out-circle me-1"></i> End</h6>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted mb-1">Date</label>
                                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" class="form-control" required>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="form-label fw-semibold small text-muted mb-1">Time</label>
                                    <select name="end_time" id="end_time" class="form-select" required>
                                        <option value="">Select Time</option>
                                        @foreach($timeOptions as $value => $label)
                                            @php
                                                $oldEndTime = old('end_time');
                                                if ($oldEndTime && strlen($oldEndTime) > 5) {
                                                    $oldEndTime = substr($oldEndTime, 0, 5);
                                                }
                                            @endphp
                                            <option value="{{ $value }}" {{ $oldEndTime == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="booked-slots-container" class="mb-4 d-none">
                <label class="form-label text-danger fw-bold"><i class="bx bx-calendar-event me-1"></i> Already Booked Slots (30-min buffer required):</label>
                <div id="booked-slots-list" class="d-flex flex-wrap gap-2">
                    <!-- Booked slots will be injected here -->
                </div>
            </div>

            <div id="availability-warning" class="alert d-none mt-2">
                <i class="bx bx-error me-1" id="availability-icon"></i>
                <span id="availability-message"></span>
            </div>

            <hr class="my-4">
            <h5 class="mb-3">Event Details</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Event Name</label>
                    <input type="text" name="event_name" value="{{ old('event_name') }}" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Department</label>
                    <input type="text" name="event_department" value="{{ old('event_department') }}" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Event Type</label>
                    <input type="text" name="event_type" value="{{ old('event_type') }}" class="form-control" required>
                </div>
            </div>

            <hr class="my-4">
            <h5 class="mb-3">Coordinator Details</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Coordinator Name</label>
                    <input type="text" name="coordinator_name" value="{{ old('coordinator_name') }}" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="coordinator_phone" value="{{ old('coordinator_phone') }}" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Department</label>
                    <input type="text" name="coordinator_department" value="{{ old('coordinator_department') }}" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email ID</label>
                    <input type="email" name="coordinator_email" value="{{ old('coordinator_email') }}" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Emergency Number</label>
                    <input type="text" name="coordinator_emergency_number" value="{{ old('coordinator_emergency_number') }}" class="form-control" required>
                </div>
            </div>

            <hr class="my-4">
            <h5 class="mb-3">Media Requirements</h5>
            <div class="row">
                @php
                    $selectedMedia = old('media_requirements', []);
                @endphp
                <div class="col-md-12 mb-3">
                    <div class="d-flex flex-wrap gap-3">
                        @foreach([
                            'photography' => 'Photography',
                            'videography' => 'Videography',
                            'livestreaming' => 'Livestreaming',
                            'reels' => 'Reels',
                            'photos' => 'Photos',
                            'others' => 'Others'
                        ] as $value => $label)
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="admin_media_{{ $value }}"
                                       name="media_requirements[]"
                                       value="{{ $value }}"
                                       {{ in_array($value, $selectedMedia, true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="admin_media_{{ $value }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-12 mb-3 {{ in_array('others', $selectedMedia, true) ? '' : 'd-none' }}" id="media_others_container">
                    <label class="form-label">Others (Please specify)</label>
                    <input type="text"
                           name="media_requirements_other"
                           value="{{ old('media_requirements_other') }}"
                           class="form-control"
                           placeholder="Enter other media requirement">
                </div>
            </div>

            <hr class="my-4">
            <h5 class="mb-3">Resources Required</h5>
            <div class="row">
                @php
                    $selectedResources = old('resources', []);
                @endphp
                <div class="col-md-12 mb-3">
                    <div class="d-flex flex-wrap gap-3">
                        @foreach([
                            'projectors' => 'Projectors',
                            'sound_systems' => 'Sound Systems',
                            'lighting' => 'Lighting',
                            'seating' => 'Seating',
                            'other' => 'Other'
                        ] as $value => $label)
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="admin_resource_{{ $value }}"
                                       name="resources[]"
                                       value="{{ $value }}"
                                       {{ in_array($value, $selectedResources, true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="admin_resource_{{ $value }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-12 mb-3 {{ in_array('other', $selectedResources, true) ? '' : 'd-none' }}" id="resources_other_container">
                    <label class="form-label">Other Resource (Please specify)</label>
                    <input type="text"
                           name="resources_other"
                           value="{{ old('resources_other') }}"
                           class="form-control"
                           placeholder="Enter other resource requirement">
                </div>
            </div>

            <div class="form-check mt-2">
                <input class="form-check-input"
                       type="checkbox"
                       value="1"
                       id="details_confirmation"
                       name="details_confirmation"
                       {{ old('details_confirmation') ? 'checked' : '' }}
                       required>
                <label class="form-check-label" for="details_confirmation">
                    I confirm that the above details are correct.
                </label>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save"></i> Save Booking
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
    const hallSelect = document.getElementById('hall_id');
    const startDateInput = document.getElementById('start_date');
    const startTimeInput = document.getElementById('start_time');
    const endDateInput = document.getElementById('end_date');
    const endTimeInput = document.getElementById('end_time');
    const warningDiv = document.getElementById('availability-warning');
    const warningMsg = document.getElementById('availability-message');
    const submitBtn = document.querySelector('button[type="submit"]');
    const bookingForm = document.querySelector('form[action="{{ route('admin.bookings.store') }}"]');

    const bookedSlotsContainer = document.getElementById('booked-slots-container');
    const bookedSlotsList = document.getElementById('booked-slots-list');

    // Debounce timer for availability checks
    let debounceTimer = null;
    let abortController = null;

    // Track if current slot is available
    let isSlotAvailable = false;
    let hasCheckedAvailability = false;

    // Set min date to today to prevent past date selection
    const today = new Date().toISOString().split('T')[0];
    startDateInput.setAttribute('min', today);
    endDateInput.setAttribute('min', today);

    // Prevent form submission if slot is not available or in the past
    bookingForm.addEventListener('submit', function(e) {
        const startDt = buildDatetime(startDateInput.value, startTimeInput.value);
        const endDt = buildDatetime(endDateInput.value, endTimeInput.value);

        // Require all fields to be filled
        if (!hallSelect.value || !startDt || !endDt) {
            e.preventDefault();
            setWarningState('error', 'Please select hall, start date/time, and end date/time.');
            return false;
        }

        // Check if selected datetime is in the past
        const now = new Date();
        const selectedStart = new Date(startDt);
        if (selectedStart < now) {
            e.preventDefault();
            setWarningState('error', 'Booking cannot be in the past. Please select a future date and time.');
            return false;
        }

        // Check if availability has been verified
        if (!hasCheckedAvailability) {
            e.preventDefault();
            setWarningState('error', 'Please wait for availability check to complete.');
            return false;
        }

        // Prevent submission if slot is not available
        if (!isSlotAvailable) {
            e.preventDefault();
            setWarningState('error', 'This slot is not available. Please select a different time.');
            return false;
        }
    });

    // Auto-set end_date when start_date changes (if end_date is empty or before start_date)
    startDateInput.addEventListener('change', function() {
        if (!endDateInput.value || endDateInput.value < startDateInput.value) {
            endDateInput.value = startDateInput.value;
        }
    });

    // Validate end date is not before start date
    endDateInput.addEventListener('change', function() {
        if (endDateInput.value < startDateInput.value) {
            endDateInput.value = startDateInput.value;
        }
    });

    function buildDatetime(dateVal, timeVal) {
        if (!dateVal || !timeVal) return null;
        return dateVal + 'T' + timeVal + ':00';
    }

    function calculateDuration() {
        const startDate = startDateInput.value;
        const startTime = startTimeInput.value;
        const endDate = endDateInput.value;
        const endTime = endTimeInput.value;

        const durationDisplay = document.getElementById('duration-display');
        const durationText = document.getElementById('duration-text');

        if (!startDate || !startTime || !endDate || !endTime) {
            durationDisplay.classList.add('d-none');
            return;
        }

        const startDt = new Date(buildDatetime(startDate, startTime));
        const endDt = new Date(buildDatetime(endDate, endTime));

        if (endDt <= startDt) {
            durationDisplay.classList.add('d-none');
            return;
        }

        const diffMs = endDt - startDt;
        const diffMins = Math.floor(diffMs / 60000);
        const days = Math.floor(diffMins / (24 * 60));
        const hours = Math.floor((diffMins % (24 * 60)) / 60);
        const minutes = diffMins % 60;

        let durationStr = '';
        if (days > 0) {
            durationStr += days + ' day' + (days > 1 ? 's' : '');
        }
        if (hours > 0) {
            if (durationStr) durationStr += ' ';
            durationStr += hours + ' hour' + (hours > 1 ? 's' : '');
        }
        if (minutes > 0) {
            if (durationStr) durationStr += ' ';
            durationStr += minutes + ' min';
        }

        durationText.textContent = durationStr || '0 min';
        durationDisplay.classList.remove('d-none');
    }

    function setWarningState(type, message) {
        warningMsg.innerText = message;
        warningDiv.classList.remove('d-none', 'alert-danger', 'alert-info', 'alert-success');
        const icon = document.getElementById('availability-icon');
        icon.className = 'bx me-1';

        if (type === 'error') {
            warningDiv.classList.add('alert-danger');
            icon.classList.add('bx-error');
        } else if (type === 'info') {
            warningDiv.classList.add('alert-info');
            icon.classList.add('bx-info-circle');
        } else if (type === 'success') {
            warningDiv.classList.add('alert-success');
            icon.classList.add('bx-check-circle');
        }
    }

    function checkAvailability() {
        // Reset availability flags when checking (user changed something)
        hasCheckedAvailability = false;
        isSlotAvailable = false;

        // Clear any pending debounced check
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }

        // Cancel any in-flight request
        if (abortController) {
            abortController.abort();
        }

        // Debounce: wait 300ms after last change before checking
        debounceTimer = setTimeout(() => {
            performAvailabilityCheck();
        }, 300);
    }

    function performAvailabilityCheck() {
        const hallId = hallSelect.value;
        const startDate = startDateInput.value;
        const startTime = startTimeInput.value;
        const endDate = endDateInput.value;
        const endTime = endTimeInput.value;

        const startDt = buildDatetime(startDate, startTime);
        const endDt = buildDatetime(endDate, endTime);

        // Clear previous results
        bookedSlotsList.innerHTML = '';
        bookedSlotsContainer.classList.add('d-none');
        warningDiv.classList.add('d-none');

        // Require hall to be selected first
        if (!hallId) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('d-none');
            return;
        }

        // Client-side validation: cannot book in the past
        if (startDt) {
            const now = new Date();
            const selectedStart = new Date(startDt);
            if (selectedStart < now) {
                setWarningState('error', 'Booking cannot be in the past. Please select a future date and time.');
                submitBtn.disabled = true;
                submitBtn.classList.remove('d-none');
                isSlotAvailable = false;
                hasCheckedAvailability = true;
                return;
            }
        }

        // Client-side validation: end must be after start
        if (startDt && endDt && endDt <= startDt) {
            setWarningState('error', 'End date/time must be after start date/time.');
            submitBtn.disabled = true;
            submitBtn.classList.remove('d-none');
            return;
        }

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Checking availability...';

        // Create new abort controller for this request
        abortController = new AbortController();

        // Build URL - always include hall_id, optionally include datetimes
        let url = `{{ route('admin.bookings.check-availability') }}?hall_id=${hallId}`;
        if (startDt) url += `&start_datetime=${encodeURIComponent(startDt)}`;
        if (endDt) url += `&end_datetime=${encodeURIComponent(endDt)}`;

        fetch(url, { signal: abortController.signal })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Render booked ranges - only show if there are actual bookings
                if (data.booked_ranges && data.booked_ranges.length > 0) {
                    data.booked_ranges.forEach(range => {
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-label-danger border border-danger p-2';
                        badge.innerHTML = `<i class="bx bx-time-five me-1"></i> ${range.start} → ${range.end} (${range.name})`;
                        bookedSlotsList.appendChild(badge);
                    });
                    bookedSlotsContainer.classList.remove('d-none');
                }

                // Only show availability result if both datetimes are selected
                if (startDt && endDt) {
                    hasCheckedAvailability = true;
                    if (!data.available) {
                        isSlotAvailable = false;
                        setWarningState('error', data.message || 'This slot is not available.');
                        submitBtn.disabled = true;
                        submitBtn.classList.add('d-none');
                    } else if (data.booked_ranges && data.booked_ranges.length > 0) {
                        // Slot is available but there are nearby bookings - show warning
                        isSlotAvailable = true;
                        setWarningState('info', 'This slot is available, but please review the nearby bookings shown above. Ensure 30-min gap is maintained.');
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('d-none');
                    } else {
                        // Slot is completely available
                        isSlotAvailable = true;
                        setWarningState('success', 'This slot is available! You can proceed with your booking.');
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('d-none');
                    }
                } else {
                    // Partial datetime info - show info message
                    const missingFields = [];
                    if (!startDt) missingFields.push('start date & time');
                    if (!endDt) missingFields.push('end date & time');
                    if (missingFields.length > 0) {
                        setWarningState('info', `Please select ${missingFields.join(' and ')} to verify availability.`);
                    }
                    submitBtn.disabled = true;
                    submitBtn.classList.remove('d-none');
                }

                // Restore submit button text
                submitBtn.innerHTML = '<i class="bx bx-save"></i> Save Booking';
            })
            .catch(error => {
                if (error.name === 'AbortError') {
                    // Request was cancelled, don't show error
                    return;
                }
                console.error('Error fetching availability:', error);
                submitBtn.innerHTML = '<i class="bx bx-save"></i> Save Booking';
                submitBtn.disabled = false;
                submitBtn.classList.remove('d-none');
            });
    }

    // Add change listeners - duration calculates immediately, availability check is debounced
    [startDateInput, startTimeInput, endDateInput, endTimeInput].forEach(el => {
        el.addEventListener('change', function() {
            calculateDuration(); // Calculate immediately for responsive UI
            checkAvailability(); // Debounced AJAX call
        });
    });

    // Hall change only triggers availability check (no duration impact)
    hallSelect.addEventListener('change', checkAvailability);

    // Initial calculation in case there are old values from failed validation
    calculateDuration();
    checkAvailability();

    // Toggle "Others" input visibility for Media Requirements
    const mediaOthersCheckbox = document.getElementById('admin_media_others');
    const mediaOthersContainer = document.getElementById('media_others_container');

    mediaOthersCheckbox.addEventListener('change', function() {
        if (this.checked) {
            mediaOthersContainer.classList.remove('d-none');
        } else {
            mediaOthersContainer.classList.add('d-none');
            mediaOthersContainer.querySelector('input').value = '';
        }
    });

    // Toggle "Other" input visibility for Resources Required
    const resourcesOtherCheckbox = document.getElementById('admin_resource_other');
    const resourcesOtherContainer = document.getElementById('resources_other_container');

    resourcesOtherCheckbox.addEventListener('change', function() {
        if (this.checked) {
            resourcesOtherContainer.classList.remove('d-none');
        } else {
            resourcesOtherContainer.classList.add('d-none');
            resourcesOtherContainer.querySelector('input').value = '';
        }
    });
});
</script>
@endsection

@extends('layouts/contentNavbarLayout')

@section('title', 'Media Bookings')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Media Dashbaord - Requests</h4>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Bookings Requiring Media Approval</h5>
            <span class="badge bg-primary text-white">{{ $bookings->count() }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Event Details</th>
                            <th>Hall</th>
                            <th>Media Requirements</th>
                            <th>Staff (Requestor)</th>
                            <th>Status Details</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($bookings->isEmpty())
                            <tr>
                                <td colspan="7" class="text-center text-muted">No media requests found.</td>
                            </tr>
                        @else
                            @foreach($bookings as $booking)
                                @php
                                    $mBadge = 'warning';
                                    if ($booking->media_status === 'accepted') $mBadge = 'success';
                                    if ($booking->media_status === 'rejected') $mBadge = 'danger';
                                    if ($booking->media_status === 'kept_pending') $mBadge = 'info';
                                @endphp
                                <tr>
                                    <td>{{ $booking->id }}</td>
                                    <td>
                                        <strong>{{ $booking->event_name }}</strong><br>
                                        <small class="text-muted">{{ $booking->formatted_datetime_range }}</small>
                                    </td>
                                    <td>{{ optional($booking->hall)->name ?? 'N/A' }}</td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($booking->media_requirements ?? [] as $req)
                                                <span class="badge bg-label-info">{{ ucfirst($req) }}</span>
                                            @endforeach
                                            @if($booking->media_requirements_other)
                                                <span class="badge bg-label-secondary" title="{{ $booking->media_requirements_other }}">More...</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ optional($booking->customer)->name ?? optional($booking->user)->name ?? 'N/A' }}</td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <span class="badge bg-label-success">Admin: Approved</span>
                                            <span class="badge bg-label-{{ $mBadge }}">Media: {{ ucfirst(str_replace('_', ' ', $booking->media_status)) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if($booking->media_status !== 'accepted')
                                                <form action="{{ route('media.bookings.updateStatus', $booking->id) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="media_status" value="accepted">
                                                    <button class="btn btn-sm btn-success" title="Accept"><i class="bx bx-check"></i> Accept</button>
                                                </form>
                                            @endif

                                            @if($booking->media_status !== 'rejected')
                                                <button type="button" 
                                                        class="btn btn-sm btn-warning btn-media-feedback" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#mediaFeedbackModal"
                                                        data-booking-id="{{ $booking->id }}"
                                                        data-action-url="{{ route('media.bookings.updateStatus', $booking->id) }}"
                                                        data-status="rejected"
                                                        data-requirements="{{ json_encode($booking->media_requirements ?? []) }}"
                                                        title="Reject (Booking remains confirmed)">
                                                    <i class="bx bx-x"></i> Reject
                                                </button>
                                            @endif

                                            @if($booking->media_status !== 'kept_pending')
                                                <button type="button" 
                                                        class="btn btn-sm btn-info btn-media-feedback" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#mediaFeedbackModal"
                                                        data-booking-id="{{ $booking->id }}"
                                                        data-action-url="{{ route('media.bookings.updateStatus', $booking->id) }}"
                                                        data-status="kept_pending"
                                                        data-requirements="{{ json_encode($booking->media_requirements ?? []) }}"
                                                        title="Keep Pending">
                                                    <i class="bx bx-time"></i> Keep Pending
                                                </button>
                                            @endif

                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Media Feedback Modal -->
<div class="modal fade" id="mediaFeedbackModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="mediaFeedbackForm" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="media_status" id="modal_media_status">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Media Action Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" id="reasonLabel">Reason for Action (Mandatory)</label>
                        <textarea name="media_feedback_reason" class="form-control" rows="3" required placeholder="Why is this action being taken?"></textarea>
                    </div>

                    <div class="mb-3" id="requirementsSection">
                        <label class="form-label">Unavailable Media Requirements (Mandatory)</label>
                        <div id="requirementsList" class="p-2 border rounded bg-light" style="max-height: 200px; overflow-y: auto;">
                            <!-- Populated via JS -->
                        </div>
                        <small class="text-muted">Select which specific requirements are NOT available or causing delay.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Remarks (Optional)</label>
                        <textarea name="media_remarks" class="form-control" rows="2" placeholder="Additional comments or explanation"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Confirm Action</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const feedbackModal = document.getElementById('mediaFeedbackModal');
    if (feedbackModal) {
        feedbackModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const actionUrl = button.getAttribute('data-action-url');
            const status = button.getAttribute('data-status');
            const requirements = JSON.parse(button.getAttribute('data-requirements'));
            
            const form = document.getElementById('mediaFeedbackForm');
            form.action = actionUrl;
            
            document.getElementById('modal_media_status').value = status;
            
            const titleEl = document.getElementById('modalTitle');
            const reasonLabelEl = document.getElementById('reasonLabel');
            const submitBtnEl = document.getElementById('submitBtn');
            
            const reqSection = document.getElementById('requirementsSection');

            if (status === 'rejected') {
                titleEl.textContent = 'Reject Media Request';
                reasonLabelEl.textContent = 'Reason for Rejection (Mandatory)';
                submitBtnEl.textContent = 'Confirm Rejection';
                submitBtnEl.className = 'btn btn-danger';
                reqSection.style.display = 'block';
            } else {
                titleEl.textContent = 'Keep Media Pending';
                reasonLabelEl.textContent = 'Reason for Pending (Mandatory)';
                submitBtnEl.textContent = 'Confirm Pending';
                submitBtnEl.className = 'btn btn-info';
                reqSection.style.display = 'none';
            }
            
            const reqList = document.getElementById('requirementsList');
            reqList.innerHTML = '';
            
            if (requirements.length > 0) {
                requirements.forEach(req => {
                    const div = document.createElement('div');
                    div.className = 'form-check mb-1';
                    div.innerHTML = `
                        <input class="form-check-input" type="checkbox" name="unavailable_media_requirements[]" value="${req}" id="req_${req}">
                        <label class="form-check-label" for="req_${req}">${req.charAt(0).toUpperCase() + req.slice(1)}</label>
                    `;
                    reqList.appendChild(div);
                });
            } else {
                reqList.innerHTML = '<p class="text-muted mb-0">No specific requirements requested.</p>';
            }
        });

        // Simple validation for the checkboxes
        document.getElementById('mediaFeedbackForm').addEventListener('submit', function(e) {
            const checkboxes = document.querySelectorAll('input[name="unavailable_media_requirements[]"]');
            if (checkboxes.length > 0) {
                let checked = false;
                checkboxes.forEach(cb => { if(cb.checked) checked = true; });
                if (!checked) {
                    alert('Please select at least one unavailable media requirement.');
                    e.preventDefault();
                }
            }
        });
    }
});
</script>
@endsection


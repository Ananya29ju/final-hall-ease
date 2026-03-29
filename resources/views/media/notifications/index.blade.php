@extends('layouts/contentNavbarLayout')

@section('title', 'Media Notifications')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">Hall Booking Notifications</h5>
                        <small class="text-muted">New bookings that may need media coordination.</small>
                    </div>
                    <span class="badge bg-warning">{{ $newBookings->count() }}</span>
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
                                    <th>Time</th>
                                    <th>Media Needs</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($newBookings as $booking)
                                    <tr>
                                        <td>#{{ $booking->id }}</td>
                                        <td>{{ $booking->customer->name ?? $booking->user->name ?? 'N/A' }}</td>
                                        <td>{{ $booking->hall->name ?? 'N/A' }}</td>
                                        <td>{{ $booking->event_name ?? 'N/A' }}</td>
                                        <td>{{ optional($booking->event_date)->format('M d, Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</td>
                                        <td>
                                            @php
                                                $mediaRequirements = collect($booking->media_requirements ?? [])
                                                    ->filter()
                                                    ->map(fn ($item) => ucfirst(str_replace('_', ' ', $item)));
                                            @endphp

                                            @if ($mediaRequirements->isNotEmpty())
                                                {{ $mediaRequirements->join(', ') }}
                                                @if (!empty($booking->media_requirements_other))
                                                    <br>
                                                    <small class="text-muted">Other: {{ $booking->media_requirements_other }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">No media requirements selected.</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No hall booking notifications found.</td>
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

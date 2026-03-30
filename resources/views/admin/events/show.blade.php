@extends('layouts/contentNavbarLayout')

@section('title', 'Event Details')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Event Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted">Booking ID</td>
                            <td><strong>#{{ $event->id }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Hall</td>
                            <td><strong>{{ $event->hall->name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Staff</td>
                            <td><strong>{{ optional($event->customer)->name ?? optional($event->user)->name ?? 'N/A' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Event Name</td>
                            <td><strong>{{ $event->event_name ?? 'N/A' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Booking Period</td>
                            <td><strong>{{ $event->formatted_datetime_range }}</strong></td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

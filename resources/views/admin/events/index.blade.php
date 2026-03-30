@extends('layouts/contentNavbarLayout')

@section('title', 'Events')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">All Events</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Booking Period</th>
                                <th>Hall</th>
                                <th>Staff</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($events as $event)
                                <tr>
                                    <td>{{ $event->event_name ?? 'N/A' }}</td>
                                    <td>{{ $event->formatted_datetime_range }}</td>
                                    <td>{{ $event->hall->name }}</td>
                                    <td>{{ optional($event->customer)->name ?? optional($event->user)->name ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('admin.events.show', $event) }}" class="btn btn-sm btn-info">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No upcoming events</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $events->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

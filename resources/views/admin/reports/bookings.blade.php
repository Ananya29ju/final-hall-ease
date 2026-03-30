@extends('layouts/contentNavbarLayout')

@section('title', 'Bookings Report')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Bookings Report</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Hall</th>
                                <th>Staff</th>
                                <th>Booking Period</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookings as $booking)
                                <tr>
                                    <td>#{{ $booking->id }}</td>
                                    <td>{{ $booking->hall->name }}</td>
                                    <td>{{ optional($booking->customer)->name ?? optional($booking->user)->name ?? 'N/A' }}</td>
                                    <td>{{ $booking->formatted_datetime_range }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $bookings->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

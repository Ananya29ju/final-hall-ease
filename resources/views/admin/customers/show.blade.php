@extends('layouts/contentNavbarLayout')

@section('title', 'Staff Details')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">Staff Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted">Name</td>
                            <td><strong>{{ $customer->name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email</td>
                            <td><strong>{{ $customer->email }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Phone</td>
                            <td><strong>{{ $customer->phone ?? 'N/A' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Member Since</td>
                            <td><strong>{{ $customer->created_at->format('M d, Y') }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Total Bookings</td>
                            <td><strong>{{ $customer->bookings_count ?? count($bookings) }}</strong></td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Booking History</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Hall</th>
                                <th>Booking Period</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bookings as $booking)
                                <tr>
                                    <td>{{ $booking->hall->name }}</td>
                                    <td>{{ $booking->formatted_datetime_range }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">No bookings found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($bookings->hasPages())
                    <div class="card-footer">
                        {{ $bookings->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

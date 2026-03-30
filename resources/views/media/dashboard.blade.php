@extends('layouts/contentNavbarLayout')

@section('title', 'Media Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-4">
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="text-muted d-block mb-1">Unread Notifications</span>
                            <h3 class="card-title mb-2">{{ $unreadCount }}</h3>
                            <small class="text-muted">Updates waiting for your attention.</small>
                        </div>
                        <span class="badge bg-label-warning p-2">
                            <i class="bx bx-bell-plus fs-4"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">Recent Notifications</h5>
                        <small class="text-muted">Stay updated with the latest booking and cancellation news.</small>
                    </div>
                    <a href="{{ route('media.notifications.index') }}" class="btn btn-sm btn-outline-primary">View All Notifications</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Message</th>
                                <th>Time</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($notifications as $notification)
                                <tr class="{{ $notification->unread() ? 'table-primary' : '' }}">
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">{{ $notification->data['event_name'] ?? 'Booking Update' }}</span>
                                            <small class="text-muted">{{ $notification->data['message'] ?? 'New notification received.' }}</small>
                                        </div>
                                    </td>
                                    <td>{{ $notification->created_at->diffForHumans() }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('media.notifications.read', $notification->id) }}" method="POST">
                                            @csrf
                                            @if($notification->unread())
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    <i class="bx bx-check-circle me-1"></i> Mark as Read
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bx bx-undo me-1"></i> Mark as Unread
                                                </button>
                                            @endif
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        <i class="bx bx-bell-off mb-2 d-block fs-1"></i>
                                        No recent notifications.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

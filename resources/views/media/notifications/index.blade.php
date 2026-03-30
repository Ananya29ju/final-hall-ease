@extends('layouts/contentNavbarLayout')

@section('title', 'Media Notifications')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Media Notifications</h5>
                    @php
                        $unreadCount = $notifications->where('read_at', null)->count();
                    @endphp
                    @if($unreadCount > 0)
                        <span class="badge bg-danger">{{ $unreadCount }} New</span>
                    @endif
                </div>
                <div class="card-body">
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
                                @forelse($notifications as $notification)
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
                                            No new notifications.
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
</div>
@endsection

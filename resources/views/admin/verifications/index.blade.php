@extends('layouts/contentNavbarLayout')

@section('title', 'Media Verification Requests')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Admin /</span> Media Verification Requests</h4>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-label-warning h-100">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-1">Pending Requests</h6>
                            <h4 class="mb-0 fw-bold">{{ $pendingCount }}</h4>
                        </div>
                        <span class="badge bg-warning p-2">
                            <i class="bx bx-time fs-4"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-label-success h-100">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-1">Approved Users</h6>
                            <h4 class="mb-0 fw-bold">{{ $approvedCount }}</h4>
                        </div>
                        <span class="badge bg-success p-2">
                            <i class="bx bx-check fs-4"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-label-danger h-100">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-1">Rejected Requests</h6>
                            <h4 class="mb-0 fw-bold">{{ $rejectedCount }}</h4>
                        </div>
                        <span class="badge bg-danger p-2">
                            <i class="bx bx-x fs-4"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Verification Requests</h5>
                <div class="btn-group">
                    <a href="{{ route('admin.verifications.index', ['status' => 'pending']) }}" class="btn btn-sm btn-{{ $currentStatus === 'pending' ? 'primary' : 'outline-primary' }}">Pending</a>
                    <a href="{{ route('admin.verifications.index', ['status' => 'approved']) }}" class="btn btn-sm btn-{{ $currentStatus === 'approved' ? 'primary' : 'outline-primary' }}">Approved</a>
                    <a href="{{ route('admin.verifications.index', ['status' => 'rejected']) }}" class="btn btn-sm btn-{{ $currentStatus === 'rejected' ? 'primary' : 'outline-primary' }}">Rejected</a>
                </div>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Registered At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone ?? 'N/A' }}</td>
                        <td>
                            @php
                                $statusClass = match($user->status) {
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    default => 'warning'
                                };
                            @endphp
                            <span class="badge bg-label-{{ $statusClass }}">{{ ucfirst($user->status) }}</span>
                        </td>
                        <td>{{ $user->created_at->format('M d, Y h:i A') }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                @if($user->status !== 'approved')
                                <form action="{{ route('admin.verifications.update', $user) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bx bx-check me-1"></i> Approve
                                    </button>
                                </form>
                                @endif
                                
                                @if($user->status !== 'rejected')
                                <form action="{{ route('admin.verifications.update', $user) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to reject this request?')">
                                        <i class="bx bx-x me-1"></i> Reject
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No verification requests found for this category.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection

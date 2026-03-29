@extends('layouts/contentNavbarLayout')

@section('title', 'Admin & Staff Management')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-4">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h4 class="mb-1">Account Management</h4>
                    <p class="mb-0 text-muted">Manage administrator, staff, and media access separately.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus"></i> Add Admin
                    </a>
                    <a href="{{ route('admin.staff.create-user') }}" class="btn btn-outline-primary">
                        <i class="bx bx-user-plus"></i> Add Staff or Media
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">Admin Accounts</h5>
                        <small class="text-muted">Users with full dashboard access.</small>
                    </div>
                    <span class="badge bg-label-primary">{{ $admins->total() }} Admin{{ $admins->total() === 1 ? '' : 's' }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($admins as $member)
                                <tr>
                                    <td>{{ $member->name }}</td>
                                    <td>{{ $member->email }}</td>
                                    <td>{{ $member->phone ?? 'N/A' }}</td>
                                    <td>{{ $member->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('admin.staff.edit', $member) }}">Edit</a>
                                                <form action="{{ route('admin.staff.destroy', $member) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No admin accounts found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $admins->appends(['staff_page' => request('staff_page'), 'media_page' => request('media_page')])->links() }}
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">Staff Accounts</h5>
                        <small class="text-muted">Users with standard staff access.</small>
                    </div>
                    <span class="badge bg-label-info">{{ $staffMembers->total() }} Staff</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($staffMembers as $member)
                                <tr>
                                    <td>{{ $member->name }}</td>
                                    <td>{{ $member->email }}</td>
                                    <td>{{ $member->phone ?? 'N/A' }}</td>
                                    <td>{{ $member->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('admin.staff.edit', $member) }}">Edit</a>
                                                <form action="{{ route('admin.staff.destroy', $member) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No staff accounts found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $staffMembers->appends(['admins_page' => request('admins_page'), 'media_page' => request('media_page')])->links() }}
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">Media Accounts</h5>
                        <small class="text-muted">Users who receive hall booking notifications for media coordination.</small>
                    </div>
                    <span class="badge bg-label-warning">{{ $mediaMembers->total() }} Media</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($mediaMembers as $member)
                                <tr>
                                    <td>{{ $member->name }}</td>
                                    <td>{{ $member->email }}</td>
                                    <td>{{ $member->phone ?? 'N/A' }}</td>
                                    <td>{{ $member->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('admin.staff.edit', $member) }}">Edit</a>
                                                <form action="{{ route('admin.staff.destroy', $member) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No media accounts found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $mediaMembers->appends(['admins_page' => request('admins_page'), 'staff_page' => request('staff_page')])->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

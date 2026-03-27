@extends('layouts/contentNavbarLayout')

@section('title', 'Admin & Staff Management')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Admin & Staff Accounts</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus"></i> Add Admin
                        </a>
                        <a href="{{ route('admin.staff.create-user') }}" class="btn btn-outline-primary">
                            <i class="bx bx-user-plus"></i> Add Staff
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Phone</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($staff as $member)
                                <tr>
                                    <td>{{ $member->name }}</td>
                                    <td>{{ $member->email }}</td>
                                    <td>
                                        @if ($member->role === 'admin')
                                            <span class="badge bg-label-primary">Admin</span>
                                        @else
                                            <span class="badge bg-label-info">Staff</span>
                                        @endif
                                    </td>
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
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $staff->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts/contentNavbarLayout')

@section('title', 'Settings')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Account Settings /</span> Settings
</h4>

<div class="row">
  <div class="col-md-12">
    @if(session('success'))
      <div class="alert alert-success">
        {{ session('success') }}
      </div>
    @endif

    <!-- Change Password -->
    <div class="card">
      <h5 class="card-header">Change Password</h5>
      <div class="card-body">
        <form method="POST" action="{{ route('profile.password') }}">
          @csrf
          @method('PATCH')
          <div class="row">
            <div class="mb-3 col-md-6 form-password-toggle">
              <label class="form-label" for="current_password">Current Password</label>
              <div class="input-group input-group-merge">
                <input class="form-control" type="password" name="current_password" id="current_password" required />
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
              </div>
              @error('current_password') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
          </div>
          <div class="row">
            <div class="mb-3 col-md-6 form-password-toggle">
              <label class="form-label" for="password">New Password</label>
              <div class="input-group input-group-merge">
                <input class="form-control" type="password" id="password" name="password" required />
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
              </div>
              @error('password') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="mb-3 col-md-6 form-password-toggle">
              <label class="form-label" for="password_confirmation">Confirm New Password</label>
              <div class="input-group input-group-merge">
                <input class="form-control" type="password" name="password_confirmation" id="password_confirmation" required />
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
              </div>
            </div>
          </div>
          <div>
            <button type="submit" class="btn btn-primary me-2">Update Password</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

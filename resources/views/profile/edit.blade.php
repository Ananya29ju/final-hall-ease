@extends('layouts/contentNavbarLayout')

@section('title', 'My Profile')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Account Settings /</span> My Profile
</h4>

<div class="row">
  <div class="col-md-12">
    @if(session('success'))
      <div class="alert alert-success">
        {{ session('success') }}
      </div>
    @endif

    <div class="card mb-4">
      <h5 class="card-header">Profile Details</h5>
      <div class="card-body">
        <form method="POST" action="{{ route('profile.update') }}">
          @csrf
          @method('PATCH')
          <div class="row">
            <div class="mb-3 col-md-6">
              <label for="name" class="form-label">Name</label>
              <input class="form-control" type="text" id="name" name="name" value="{{ old('name', $user->name) }}" autofocus required />
              @error('name') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="mb-3 col-md-6">
              <label for="email" class="form-label">E-mail</label>
              <input class="form-control" type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required />
              @error('email') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="mb-3 col-md-6">
              <label class="form-label" for="phone">Phone Number</label>
              <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" />
              @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="mb-3 col-md-6">
              <label for="role" class="form-label">Role</label>
              <input type="text" class="form-control" id="role" name="role" value="{{ ucfirst($user->role) }}" disabled />
            </div>
          </div>
          <div class="mt-2">
            <button type="submit" class="btn btn-primary me-2">Save changes</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>
@endsection

@extends('layouts/contentNavbarLayout')

@section('title', 'Add New Hall')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Add New Hall</h5>
                </div>
                <form action="{{ route('admin.halls.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="name" class="form-label">Hall Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required />
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="campus_name" class="form-label">Campus Name *</label>
                            <select class="form-control @error('campus_name') is-invalid @enderror" id="campus_name" name="campus_name" required>
                                <option value="">Select Campus</option>
                                <option value="Main Campus" {{ old('campus_name') === 'Main Campus' ? 'selected' : '' }}>Main Campus</option>
                                <option value="AIMIT Campus" {{ old('campus_name') === 'AIMIT Campus' ? 'selected' : '' }}>AIMIT Campus</option>
                                <option value="Engineering Campus" {{ old('campus_name') === 'Engineering Campus' ? 'selected' : '' }}>Engineering Campus</option>
                                <option value="Capitano Campus" {{ old('campus_name') === 'Capitano Campus' ? 'selected' : '' }}>Capitano Campus</option>
                            </select>
                            @error('campus_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="location" class="form-label">Block *</label>
                            <select class="form-control @error('location') is-invalid @enderror" id="location" name="location" required>
                                <option value="">Select Block</option>
                                <option value="Admin" {{ old('location') === 'Admin' ? 'selected' : '' }}>Admin</option>
                                <option value="Aruppe" {{ old('location') === 'Aruppe' ? 'selected' : '' }}>Aruppe</option>
                                <option value="Maffie" {{ old('location') === 'Maffie' ? 'selected' : '' }}>Maffie</option>
                                <option value="Xavier" {{ old('location') === 'Xavier' ? 'selected' : '' }}>Xavier</option>
                                <option value="LCRI" {{ old('location') === 'LCRI' ? 'selected' : '' }}>LCRI</option>
                            </select>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="capacity" class="form-label">Capacity *</label>
                            <input type="number" class="form-control @error('capacity') is-invalid @enderror" id="capacity" name="capacity" value="{{ old('capacity') }}" required />
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="available" {{ old('status') === 'available' ? 'selected' : '' }}>Available</option>
                                <option value="maintenance" {{ old('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Hall Images</label>
                            <div id="hall-images-container">
                                <input type="file" class="form-control mb-2 @error('hall_images') is-invalid @enderror @error('hall_images.*') is-invalid @enderror" name="hall_images[]" accept=".jpg,.jpeg,.png,.webp,.heic,.heif" />
                            </div>
                            <button type="button" id="add-hall-image-input" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="bx bx-plus"></i> Add Another Image
                            </button>
                            <small class="text-muted">You can select up to 20 images (JPG, PNG, WEBP, HEIC, HEIF up to 10MB each).</small>
                            @error('hall_images')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('hall_images.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('admin.halls.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Hall</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addButton = document.getElementById('add-hall-image-input');
        const container = document.getElementById('hall-images-container');

        addButton.addEventListener('click', function () {
            const inputs = container.querySelectorAll('input[type="file"]').length;
            if (inputs >= 20) return;

            const input = document.createElement('input');
            input.type = 'file';
            input.name = 'hall_images[]';
            input.accept = '.jpg,.jpeg,.png,.webp,.heic,.heif';
            input.className = 'form-control mb-2';
            container.appendChild(input);
        });
    });
</script>
@endsection

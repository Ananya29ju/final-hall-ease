@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Hall')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Edit Hall</h5>
                </div>
                @if(session('success'))
                    <div class="alert alert-success mx-3 mt-3 mb-0">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger mx-3 mt-3 mb-0">{{ session('error') }}</div>
                @endif

                <form action="{{ route('admin.halls.update', $hall) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="name" class="form-label">Hall Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $hall->name) }}" required />
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="campus_name" class="form-label">Campus Name *</label>
                            <select class="form-control @error('campus_name') is-invalid @enderror" id="campus_name" name="campus_name" required>
                                <option value="">Select Campus</option>
                                <option value="Main Campus" {{ old('campus_name', $hall->campus_name) === 'Main Campus' ? 'selected' : '' }}>Main Campus</option>
                                <option value="AIMIT Campus" {{ old('campus_name', $hall->campus_name) === 'AIMIT Campus' ? 'selected' : '' }}>AIMIT Campus</option>
                                <option value="Engineering Campus" {{ old('campus_name', $hall->campus_name) === 'Engineering Campus' ? 'selected' : '' }}>Engineering Campus</option>
                                <option value="Capitano Campus" {{ old('campus_name', $hall->campus_name) === 'Capitano Campus' ? 'selected' : '' }}>Capitano Campus</option>
                            </select>
                            @error('campus_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="location" class="form-label">Block *</label>
                            <select class="form-control @error('location') is-invalid @enderror" id="location" name="location" required>
                                <option value="">Select Block</option>
                                <option value="Admin" {{ old('location', $hall->location) === 'Admin' ? 'selected' : '' }}>Admin</option>
                                <option value="Aruppe" {{ old('location', $hall->location) === 'Aruppe' ? 'selected' : '' }}>Aruppe</option>
                                <option value="Maffie" {{ old('location', $hall->location) === 'Maffie' ? 'selected' : '' }}>Maffie</option>
                                <option value="Xavier" {{ old('location', $hall->location) === 'Xavier' ? 'selected' : '' }}>Xavier</option>
                                <option value="LCRI" {{ old('location', $hall->location) === 'LCRI' ? 'selected' : '' }}>LCRI</option>
                            </select>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="capacity" class="form-label">Capacity *</label>
                            <input type="number" class="form-control @error('capacity') is-invalid @enderror" id="capacity" name="capacity" value="{{ old('capacity', $hall->capacity) }}" required />
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $hall->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="available" {{ old('status', $hall->status) === 'available' ? 'selected' : '' }}>Available</option>
                                <option value="maintenance" {{ old('status', $hall->status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Add More Hall Images</label>
                            <div id="hall-images-container">
                                <input type="file" class="form-control mb-2 @error('hall_images') is-invalid @enderror @error('hall_images.*') is-invalid @enderror" name="hall_images[]" accept=".jpg,.jpeg,.png,.webp,.heic,.heif" />
                            </div>
                            <button type="button" id="add-hall-image-input" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="bx bx-plus"></i> Add Another Image
                            </button>
                            <small class="text-muted">You can upload up to 20 images (JPG, PNG, WEBP, HEIC, HEIF up to 10MB each).</small>
                            @error('hall_images')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('hall_images.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('admin.halls.index') }}" class="btn btn-secondary">Back</a>
                        <button type="submit" class="btn btn-primary">Update Hall</button>
                    </div>
                </form>

                <div class="card-body border-top">
                    <div class="form-group mb-0">
                        <label class="form-label">Existing Hall Images</label>
                        @if($hall->images->count() > 0)
                            <div class="row g-3">
                                @foreach($hall->images as $image)
                                    <div class="col-md-4 col-sm-6">
                                        <div class="border rounded p-2">
                                            <img src="{{ asset('storage/' . $image->image_path) }}" alt="Hall image" class="img-fluid rounded mb-2" style="height: 150px; width: 100%; object-fit: cover;">
                                            <form action="{{ route('admin.halls.images.destroy', [$hall, $image]) }}" method="POST" onsubmit="return confirm('Delete this image?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                                    <i class="bx bx-trash"></i> Delete Photo
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-muted">No hall images uploaded.</div>
                        @endif
                    </div>
                </div>
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

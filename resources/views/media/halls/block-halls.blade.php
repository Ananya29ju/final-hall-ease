@extends('layouts/contentNavbarLayout')

@section('title', 'Block Halls')

@section('page-style')
<style>
    .hall-tile {
        border: 1px solid #e7ebf2;
        border-radius: 16px;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        background: linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
    }

    .hall-tile:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 24px rgba(50, 70, 100, 0.12);
    }

    .hall-image-wrap {
        height: 180px;
        background: radial-gradient(circle at top, #f2f6ff 0%, #eef2f8 75%);
        border-bottom: 1px solid #edf1f7;
        padding: 0;
        position: relative;
        overflow: hidden;
    }

    .hall-image {
        width: 100%;
        height: 180px;
        object-fit: cover;
    }

    .hall-carousel {
        width: 100%;
        height: 100%;
    }

    .hall-carousel .carousel-item {
        height: 180px;
    }

    .hall-carousel .carousel-control-prev,
    .hall-carousel .carousel-control-next {
        width: 14%;
    }

    .hall-carousel .carousel-control-prev-icon,
    .hall-carousel .carousel-control-next-icon {
        background-color: rgba(0, 0, 0, 0.45);
        border-radius: 50%;
        background-size: 55% 55%;
    }

    .hall-image-count {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 3;
    }

    .hall-image-placeholder {
        width: 100%;
        height: 100%;
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #7b8ba1;
        background: #f5f7fb;
        border: 1px dashed #d5ddea;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .hall-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2f3349;
    }

    .hall-footer-btn {
        border-radius: 0;
        border: 0;
        padding: 0.85rem 1rem;
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: 0.01em;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-body">
            <h4 class="mb-1">{{ $campus }}</h4>
            <p class="text-muted mb-0">Block: {{ $block }}</p>
        </div>
    </div>

    <div class="row">
        @forelse ($halls as $hall)
            <div class="col-xl-3 col-md-4 col-sm-6 mb-4">
                <div class="card hall-tile h-100">
                    <div class="hall-image-wrap">
                        @php
                            $imageUrls = $hall->images
                                ->pluck('image_path')
                                ->filter()
                                ->map(fn ($path) => asset('storage/' . ltrim($path, '/')))
                                ->values()
                                ->all();

                            if (empty($imageUrls) && !empty($hall->image)) {
                                $legacyPath = ltrim($hall->image, '/');
                                $imageUrls[] = str_starts_with($legacyPath, 'halls/')
                                    ? asset('storage/' . $legacyPath)
                                    : asset($legacyPath);
                            }
                        @endphp

                        @if (count($imageUrls) > 1)
                            <span class="badge bg-dark hall-image-count">{{ count($imageUrls) }} Photos</span>
                            <div id="hallCarousel{{ $hall->id }}" class="carousel slide hall-carousel" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    @foreach ($imageUrls as $index => $imageUrl)
                                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                            <img src="{{ $imageUrl }}" alt="{{ $hall->name }} image {{ $index + 1 }}" class="hall-image">
                                        </div>
                                    @endforeach
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#hallCarousel{{ $hall->id }}" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#hallCarousel{{ $hall->id }}" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        @elseif (count($imageUrls) === 1)
                            <img src="{{ $imageUrls[0] }}" alt="{{ $hall->name }}" class="hall-image">
                        @else
                            <div class="hall-image-placeholder">
                                <i class="bx bx-image-alt mb-1" style="font-size: 1.4rem;"></i>
                                <span>No image uploaded</span>
                            </div>
                        @endif
                    </div>

                    <div class="card-body">
                        <h5 class="hall-title mb-2">{{ $hall->name }}</h5>
                        <p class="text-muted mb-2">Capacity: {{ $hall->capacity }} persons</p>
                        <span class="badge bg-{{ $hall->status === 'available' ? 'success' : 'warning' }} mb-1">
                            {{ ucfirst($hall->status) }}
                        </span>
                    </div>

                    @if ($hall->status === 'available')
                        <div class="px-3 pb-3">
                            <button type="button" class="btn btn-primary hall-footer-btn w-100" style="opacity: 0.9; cursor: default;">Available</button>
                        </div>
                    @else
                        <button type="button" class="btn btn-secondary hall-footer-btn w-100" disabled>Currently Unavailable</button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info mb-0">
                    No halls found in this block.
                </div>
            </div>
        @endforelse
    </div>

    @if ($halls->hasPages())
        <div class="mt-2">
            {{ $halls->links() }}
        </div>
    @endif
</div>
@endsection

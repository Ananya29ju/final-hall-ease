@extends('layouts/contentNavbarLayout')

@section('title', 'Browse Halls')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-body">
            <h4 class="mb-1">Browse Halls by Campus</h4>
            <p class="text-muted mb-0">Select a campus and block to view available halls.</p>
        </div>
    </div>

    <div class="row">
        @forelse ($campusGroups as $campus => $blocks)
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">{{ $campus }}</h5>
                    </div>
                    <div class="card-body">
                        @if ($blocks->isNotEmpty())
                            @if ($campus === 'Main Campus')
                                <div class="dropdown">
                                    <button class="btn btn-outline-primary dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" id="dropdownMenuButton{{ str($campus)->slug() }}" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Select Block
                                    </button>
                                    <div class="dropdown-menu w-100" aria-labelledby="dropdownMenuButton{{ str($campus)->slug() }}">
                                        @foreach ($blocks as $block)
                                            <a class="dropdown-item" href="{{ route('media.halls.block', ['campus' => $campus, 'block' => $block]) }}">{{ $block }}</a>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($blocks as $block)
                                        <a href="{{ route('media.halls.block', ['campus' => $campus, 'block' => $block]) }}"
                                           class="btn btn-outline-primary btn-sm">
                                            {{ $block }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <p class="text-muted mb-0">No blocks available.</p>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info mb-0">
                    No campus halls configured yet.
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection

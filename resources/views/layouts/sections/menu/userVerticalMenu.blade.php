@php
    use Illuminate\Support\Facades\Route;
@endphp
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    <div class="app-brand demo">
        <a href="{{ url('/') }}" class="app-brand-link">
            <span class="app-brand-logo demo">@include('_partials.macros')</span>
            <span class="app-brand-text demo menu-text fw-bold ms-2">{{ config('variables.templateName') }}</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="icon-base bx bx-chevron-left icon-sm d-flex align-items-center justify-content-center"></i>
        </a>
    </div>

    <div class="menu-divider mt-0"></div>
    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1 d-flex flex-column h-100">

        <li class="menu-item mb-2 {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
            <a href="{{ route('user.dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div>Dashboard</div>
            </a>
        </li>

        <li class="menu-item mb-2 {{ request()->routeIs('user.halls.*') ? 'active' : '' }}">
            <a href="{{ route('user.halls.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-buildings"></i>
                <div>Browse Halls</div>
            </a>
        </li>

        <li class="menu-item mb-2 {{ request()->routeIs('user.bookings.index') ? 'active' : '' }}">
            <a href="{{ route('user.bookings.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-calendar"></i>
                <div>My Bookings</div>
            </a>
        </li>

        <li class="menu-item mb-2 {{ request()->routeIs('user.bookings.create') ? 'active' : '' }}">
            <a href="{{ route('user.bookings.create') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-plus-circle"></i>
                <div>New Booking</div>
            </a>
        </li>

        <li class="menu-item mb-2 {{ request()->routeIs('user.bookings.cancel.*') ? 'active' : '' }}">
            <a href="{{ route('user.bookings.cancel.form') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-x-circle"></i>
                <div>Cancel Booking</div>
            </a>
        </li>

        <li class="menu-item mb-2 {{ request()->routeIs('user.notifications.*') ? 'active' : '' }}">
            <a href="{{ route('user.notifications.index') }}" class="menu-link d-flex justify-content-between align-items-center">
                <span>
                    <i class="menu-icon tf-icons bx bx-bell"></i>
                    Notifications
                </span>
                @if(($user_notification_count ?? 0) > 0)
                    <span class="badge bg-danger rounded-pill">{{ $user_notification_count }}</span>
                @endif
            </a>
        </li>


    </ul>
</aside>

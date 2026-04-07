@php
    use Illuminate\Support\Facades\Route;
@endphp
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    <!-- ! Hide app brand if navbar-full -->
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

        {{-- Dashboard --}}
        <li class="menu-item mb-2 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div>Dashboard</div>
            </a>
        </li>

        {{-- Hall Management --}}
        <li class="menu-item mb-2 {{ request()->routeIs('admin.halls.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-buildings"></i>
                <div>Hall Management</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item mb-2 {{ request()->routeIs('admin.halls.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.halls.index') }}" class="menu-link">
                        <div>All Halls</div>
                    </a>
                </li>
                <li class="menu-item mb-2 {{ request()->routeIs('admin.halls.browse') || request()->routeIs('admin.halls.block') ? 'active' : '' }}">
                    <a href="{{ route('admin.halls.browse') }}" class="menu-link">
                        <div>Browse Halls</div>
                    </a>
                </li>
                <li class="menu-item mb-2 {{ request()->routeIs('admin.halls.create') ? 'active' : '' }}">
                    <a href="{{ route('admin.halls.create') }}" class="menu-link">
                        <div>Add Hall</div>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Bookings --}}
        <li class="menu-item mb-2 {{ request()->routeIs('admin.bookings.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-calendar"></i>
                <div>Bookings</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item mb-2 {{ request()->routeIs('admin.bookings.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.bookings.index') }}" class="menu-link">
                        <div>All Bookings</div>
                    </a>
                </li>
                <li class="menu-item mb-2 {{ request()->routeIs('admin.bookings.create') ? 'active' : '' }}">
                    <a href="{{ route('admin.bookings.create') }}" class="menu-link">
                        <div>New Booking</div>
                    </a>
                </li>
                <li class="menu-item mb-2 {{ request()->routeIs('admin.bookings.cancel.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.bookings.cancel.form') }}" class="menu-link">
                        <div>Cancellation</div>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Notifications --}}
        <li class="menu-item mb-2 {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
            <a href="{{ route('admin.notifications.index') }}" class="menu-link d-flex justify-content-between align-items-center">
                <span>
                    <i class="menu-icon tf-icons bx bx-bell"></i>
                    Notifications
                </span>
                @if(($admin_notification_count ?? 0) > 0)
                    <span class="badge bg-danger rounded-pill">{{ $admin_notification_count }}</span>
                @endif
            </a>
        </li>

        {{-- Staff --}}
        <li class="menu-item mb-2 {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
            <a href="{{ route('admin.staff.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-group"></i>
                <div>Staff Management</div>
            </a>
        </li>

        {{-- Settings --}}
        <li class="menu-item mb-2 {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <a href="{{ route('admin.settings.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div>Settings</div>
            </a>
        </li>



    </ul>

</aside>

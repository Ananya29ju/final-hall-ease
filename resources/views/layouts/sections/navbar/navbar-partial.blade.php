@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
$userInitial = strtoupper(substr(Auth::user()->name ?? 'U', 0, 1));
@endphp

<!--  Brand demo (display only for navbar-full and hide on below xl) -->
@if(isset($navbarFull))
<div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
    <a href="{{url('/')}}" class="app-brand-link gap-2">
        <span class="app-brand-logo demo">@include('_partials.macros')</span>
        <span class="app-brand-text demo menu-text fw-bold text-heading">{{config('variables.templateName')}}</span>
    </a>
</div>
@endif

<!-- ! Not required for layout-without-menu -->
@if(!isset($navbarHideToggle))
<div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 {{ isset($contentNavbar) ?' d-xl-none ' : '' }}">
    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
        <i class="icon-base bx bx-menu icon-md"></i>
    </a>
</div>
@endif

<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    <ul class="navbar-nav flex-row align-items-center w-100 gap-2">



        <!-- Expand Icon -->
        <li class="nav-item me-2">
            <a class="nav-link" href="javascript:void(0);" onclick="toggleFullScreen()">
                <i class="bx bx-expand icon-md navbar-search-icon"></i>
            </a>
        </li>

        @forelse (collect($campus_groups ?? []) as $campusName => $blocks)
            <li class="nav-item dropdown me-2">
                <a class="nav-link dropdown-toggle" href="javascript:void(0);" data-bs-toggle="dropdown">
                    {{ $campusName }}
                </a>
                <ul class="dropdown-menu">
                    @forelse ($blocks as $blockName => $halls)
                        <li>
                            @php
                                $role = Auth::user()->role;
                                if ($role === 'admin') {
                                    $blockRoute = route('admin.halls.block', ['campus' => $campusName, 'block' => $blockName]);
                                } elseif ($role === 'media') {
                                    $blockRoute = route('media.halls.block', ['campus' => $campusName, 'block' => $blockName]);
                                } else {
                                    $blockRoute = route('user.halls.block', ['campus' => $campusName, 'block' => $blockName]);
                                }
                            @endphp
                            <a class="dropdown-item" href="{{ $blockRoute }}">
                                {{ $blockName }}
                            </a>
                        </li>
                    @empty
                        <li>
                            <span class="dropdown-item text-muted">No blocks added</span>
                        </li>
                    @endforelse
                </ul>
            </li>
        @empty
        @endforelse

        <!-- User -->
        <li class="nav-item navbar-dropdown dropdown-user dropdown ms-auto">
            <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                <div class="avatar avatar-online">
                    <span class="w-px-40 h-px-40 rounded-circle d-flex align-items-center justify-content-center fw-semibold text-white" style="background: rgba(255,255,255,0.25); border: 2px solid rgba(255,255,255,0.5);">
                        {{ $userInitial }}
                    </span>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="javascript:void(0);">
                        <div class="d-flex">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar avatar-online">
                                    <span class="w-px-40 h-px-40 rounded-circle d-flex align-items-center justify-content-center fw-semibold text-white bg-primary">
                                        {{ $userInitial }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ Auth::user()->name ?? 'User' }}</h6>
                                <small class="text-muted">{{ ucfirst(Auth::user()->role ?? 'user') }}</small>
                            </div>
                        </div>
                    </a>
                </li>
                <li>
                    <div class="dropdown-divider my-1"></div>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="icon-base bx bx-user icon-md me-3"></i><span>My Profile</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('profile.settings') }}">
                        <i class="icon-base bx bx-cog icon-md me-3"></i><span>Settings</span>
                    </a>
                </li>
                <li>
                    <div class="dropdown-divider my-1"></div>
                </li>

                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item border-0 bg-transparent w-100 text-start">
                            <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Log Out</span>
                        </button>
                    </form>
                </li>
            </ul>
        </li>
        <!--/ User -->
    </ul>
</div>

<script>
function toggleFullScreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
    } else if (document.exitFullscreen) {
        document.exitFullscreen();
    }
}
</script>



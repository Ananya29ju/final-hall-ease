@extends('layouts/blankLayout')

@section('title', 'Verify Your Email – HallEase')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
@php
    $resendCount  = session('reg_resend_count', 0);
    $limitReached = $resendCount >= 3;
@endphp

<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
            <div class="card px-sm-6 px-0">
                <div class="card-body">

                    {{-- Logo --}}
                    <div class="app-brand justify-content-center mb-6">
                        <a href="{{ url('/') }}" class="app-brand-link gap-2">
                            <span class="app-brand-logo demo">@include('_partials.macros')</span>
                            <span class="app-brand-text demo text-heading fw-bold">{{ config('variables.templateName') }}</span>
                        </a>
                    </div>

                    @if ($limitReached)
                    {{-- ═══════════════════════════════════════════
                         BLOCKED STATE – entire body replaced
                    ════════════════════════════════════════════ --}}
                        <div class="text-center mb-5">
                            <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#ff4d4d,#ff7675);display:inline-flex;align-items:center;justify-content:center;box-shadow:0 8px 24px rgba(255,77,77,.3);">
                                <i class="bx bx-block" style="font-size:34px;color:#fff;"></i>
                            </div>
                        </div>

                        <h4 class="text-center mb-3">Verification Blocked</h4>

                        <div class="alert alert-warning text-center p-4 mb-4" style="font-size:.95rem;">
                            <i class="bx bx-error-circle fs-4 d-block mb-2"></i>
                            <strong>Max resend limit reached.</strong><br>
                            Please contact the admin for assistance.
                        </div>

                        <p class="text-center mb-0">
                            <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bx bx-chevron-left"></i> Back to Login
                            </a>
                        </p>

                    @else
                    {{-- ═══════════════════════════════════════════
                         NORMAL STATE – OTP form
                    ════════════════════════════════════════════ --}}

                        {{-- Icon --}}
                        <div class="text-center mb-4">
                            <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#696cff,#7c7fff);display:inline-flex;align-items:center;justify-content:center;box-shadow:0 8px 24px rgba(105,108,255,.35);">
                                <i class="bx bx-envelope-open" style="font-size:32px;color:#fff;"></i>
                            </div>
                        </div>

                        <h4 class="mb-1 text-center">Verify Your Email ✉️</h4>
                        <p class="mb-5 text-center text-muted" style="font-size:.93rem;">
                            We sent a <strong>6-digit OTP</strong> to your institutional email.<br>
                            Please enter it below to complete registration.
                        </p>

                        {{-- Flash alerts --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                @foreach ($errors->all() as $err)
                                    <div>{{ $err }}</div>
                                @endforeach
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        {{-- OTP form --}}
                        <form id="formOtp" action="{{ route('register.verify.submit') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label for="otp" class="form-label fw-semibold">Enter OTP</label>
                                <input
                                    type="text"
                                    id="otp"
                                    name="otp"
                                    class="form-control form-control-lg text-center @error('otp') is-invalid @enderror"
                                    maxlength="6"
                                    inputmode="numeric"
                                    placeholder="— — — — — —"
                                    autocomplete="one-time-code"
                                    autofocus
                                    style="letter-spacing:.6rem;font-size:1.8rem;font-weight:700;"
                                />
                                @error('otp')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- 2-minute countdown --}}
                            <p class="text-center text-muted mb-4" style="font-size:.88rem;">
                                OTP expires in:&nbsp;<span id="countdown" class="fw-bold text-primary">02:00</span>
                            </p>

                            <button type="submit" class="btn btn-primary d-grid w-100 mb-3" id="verifyBtn">
                                <span><i class="bx bx-check-shield me-1"></i> Verify &amp; Create Account</span>
                            </button>
                        </form>

                        {{-- Resend button --}}
                        <div class="text-center">
                            <p class="text-muted mb-2" style="font-size:.9rem;">Didn't receive the email?</p>
                            <form action="{{ route('register.verify.resend') }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary btn-sm">
                                    <i class="bx bx-refresh me-1"></i> Resend OTP
                                </button>
                            </form>
                        </div>

                        <p class="text-center mt-4 mb-0">
                            <a href="{{ route('login') }}">
                                <i class="bx bx-chevron-left"></i> Back to Login
                            </a>
                        </p>

                    @endif
                    {{-- end if $limitReached --}}

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
@if (!$limitReached)
<script>
(function () {
    'use strict';

    // Digits-only filter
    var otpEl = document.getElementById('otp');
    if (otpEl) {
        otpEl.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
        });
    }

    // 2-minute countdown (120 seconds)
    var total     = 2 * 60;
    var countEl   = document.getElementById('countdown');
    var verifyBtn = document.getElementById('verifyBtn');

    function fmt(n) { return String(n).padStart(2, '0'); }

    var timer = setInterval(function () {
        total -= 1;

        if (total <= 0) {
            clearInterval(timer);
            countEl.textContent = '00:00';
            countEl.className   = 'fw-bold text-danger';
            verifyBtn.disabled  = true;
            verifyBtn.innerHTML = '<span><i class="bx bx-time-five me-1"></i> OTP Expired &ndash; Please Resend</span>';
            return;
        }

        countEl.textContent = fmt(Math.floor(total / 60)) + ':' + fmt(total % 60);

        // Turn red in last 30 seconds
        if (total <= 30) {
            countEl.className = 'fw-bold text-danger';
        }
    }, 1000);
})();
</script>
@endif
@endsection

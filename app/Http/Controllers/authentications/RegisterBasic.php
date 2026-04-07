<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\EmailVerificationOtp;
use App\Notifications\NewMediaUserRegistered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

class RegisterBasic extends Controller
{
    // ──────────────────────────────────────────────
    // STEP 1 – Show registration form
    // ──────────────────────────────────────────────
    public function index()
    {
        if (Auth::check()) {
            return $this->redirectDashboard();
        }

        return view('auth.register');
    }

    /**
     * Dashboard redirection helper
     */
    private function redirectDashboard()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $dashboardRoute = match (true) {
            $user->isAdmin() => 'admin.dashboard',
            $user->isMedia() => 'media.dashboard',
            default          => 'user.dashboard',
        };

        return redirect()->route($dashboardRoute);
    }

    // ──────────────────────────────────────────────
    // STEP 2 – Validate form, store in session,
    //          send OTP, redirect to verify page
    // ──────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => [
                'required',
                'email',
                'unique:users,email',
                function ($attribute, $value, $fail) {
                    if (!str_ends_with(strtolower($value), '@staloysius.edu.in')) {
                        $fail('Only institutional email addresses ending with @staloysius.edu.in are allowed.');
                    }
                },
            ],
            'phone'    => 'required|string|max:20',
            'role'     => 'required|in:user,media',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Generate a 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Clear any previous pending registration & resend counter
        session()->forget(['reg_pending', 'reg_resend_count']);

        // Store registration data + OTP in session
        session([
            'reg_pending' => [
                'name'       => $validated['name'],
                'email'      => $validated['email'],
                'phone'      => $validated['phone'] ?? null,
                'role'       => $validated['role'],
                'password'   => Hash::make($validated['password']),
                'otp'        => $otp,
                'otp_expiry' => now()->addMinutes(2)->timestamp,
            ],
            'reg_resend_count' => 0,
        ]);

        // Send OTP email
        Notification::route('mail', $validated['email'])
            ->notify(new EmailVerificationOtp($otp, $validated['name']));

        return redirect()->route('register.verify')
            ->with('success', 'A verification OTP has been sent to ' . $validated['email'] . '. Please check your inbox.');
    }

    // ──────────────────────────────────────────────
    // STEP 3 – Show OTP verification page
    // ──────────────────────────────────────────────
    public function showVerify()
    {
        // 1. If already logged in, go to dashboard
        if (Auth::check()) {
            return $this->redirectDashboard();
        }

        // 2. If no registration in progress, go back to login
        if (!session()->has('reg_pending')) {
            return redirect()->route('login')
                ->with('error', 'Session expired. Please start the registration process again.');
        }

        return response()
            ->view('auth.verify-otp')
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
    }

    // ──────────────────────────────────────────────
    // STEP 4 – Verify OTP & create the account
    // ──────────────────────────────────────────────
    public function verifyOtp(Request $request)
    {
        if (Auth::check()) {
            return $this->redirectDashboard();
        }

        $request->validate([
            'otp' => 'required|digits:6',
        ], [
            'otp.required' => 'Please enter the OTP sent to your email.',
            'otp.digits'   => 'OTP must be exactly 6 digits.',
        ]);

        $pending = session('reg_pending');

        if (!$pending) {
            return redirect()->route('register')
                ->with('error', 'Session expired. Please register again.');
        }

        // Check OTP expiry (10 minutes)
        if (now()->timestamp > $pending['otp_expiry']) {
            session()->forget(['reg_pending', 'reg_resend_count']);
            return redirect()->route('register')
                ->with('error', 'Your OTP has expired. Please register again to get a new OTP.');
        }

        // Check OTP value
        if ($request->otp !== $pending['otp']) {
            return back()->withErrors(['otp' => 'Invalid OTP. Please try again.']);
        }

        // OTP is correct – create the account
        try {
            $isMedia = $pending['role'] === 'media';

            $user = User::create([
                'name'              => $pending['name'],
                'email'             => $pending['email'],
                'phone'             => $pending['phone'],
                'password'          => $pending['password'], // already hashed
                'role'              => $pending['role'],
                'status'            => $isMedia ? 'pending' : 'approved',
                'email_verified_at' => now(),
            ]);

            // Clear session completely
            session()->forget(['reg_pending', 'reg_resend_count']);

            if ($isMedia) {
                $admins = User::where('role', 'admin')->get();
                Notification::send($admins, new NewMediaUserRegistered($user));

                return redirect()->route('login')
                    ->with('success', 'Your email has been verified! Your account is under admin review. Please wait for approval.');
            }

            Auth::login($user);

            return redirect()->route('user.dashboard')
                ->with('success', 'Email verified! Welcome to HallEase.');

        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Something went wrong while creating your account. Please try again.');
        }
    }

    // ──────────────────────────────────────────────
    // STEP 5 – Resend OTP (max 3 times)
    // ──────────────────────────────────────────────
    public function resendOtp(Request $request)
    {
        if (Auth::check()) {
            return $this->redirectDashboard();
        }

        $pending = session('reg_pending');

        if (!$pending) {
            return redirect()->route('register')
                ->with('error', 'No pending registration found. Please fill the form again.');
        }

        $resendCount = session('reg_resend_count', 0);

        // Block after 3 resend attempts
        if ($resendCount >= 3) {
            return back()->with('resend_blocked', true);
        }

        // Generate new OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        session()->put('reg_pending.otp', $otp);
        session()->put('reg_pending.otp_expiry', now()->addMinutes(2)->timestamp);
        session()->put('reg_resend_count', $resendCount + 1);

        // Send new OTP email
        Notification::route('mail', $pending['email'])
            ->notify(new EmailVerificationOtp($otp, $pending['name']));

        return back()->with('success', 'A new OTP has been sent to ' . $pending['email'] . '.');
    }
}

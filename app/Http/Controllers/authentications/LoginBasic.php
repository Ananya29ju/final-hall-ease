<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginBasic extends Controller
{
    /**
     * Show the login form
     */
    public function index()
    {
        if (Auth::check()) {
            $dashboardRoute = match (true) {
                Auth::user()->isAdmin() => 'admin.dashboard',
                Auth::user()->isMedia() => 'media.dashboard',
                default => 'user.dashboard',
            };

            return redirect()->route($dashboardRoute);
        }

        return view('auth.login');
    }

    /**
     * Handle user login
     */
    public function store(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'Email is required',
            'email.email' => 'Please enter a valid email',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 6 characters',
        ]);

        // Attempt to login
        if (Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            $user = Auth::user();

            // Check if account is approved
            if (!$user->isApproved()) {
                $status = $user->status;
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = 'Your account is not approved yet.';
                if ($status === 'pending') {
                    $message = 'Your account is under verification. Please wait for admin approval.';
                } elseif ($status === 'rejected') {
                    $message = 'Your account request has been rejected. Please contact admin.';
                }

                return redirect()->route('login')->with('error', $message);
            }

            // Regenerate session
            $request->session()->regenerate();

            $dashboardRoute = match (true) {
                $user->isAdmin() => 'admin.dashboard',
                $user->isMedia() => 'media.dashboard',
                default => 'user.dashboard',
            };

            // Redirect to role-specific dashboard
            return redirect()->intended(route($dashboardRoute))
                ->with('success', 'Login successful! Welcome back.');
        }

        // Login failed
        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Invalid email or password.');
    }

    /**
     * Logout the user
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')
            ->with('success', 'You have been logged out successfully.');
    }
}

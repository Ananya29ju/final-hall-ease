<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterBasic extends Controller
{
    /**
     * Show the registration form
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

        return view('auth.register');
    }

    /**
     * Handle user registration
     */
    public function store(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:user,media',
            'password' => 'required|string|min:6|confirmed',
        ]);

        try {
            $isMedia = $validated['role'] === 'media';
            
            // Create new user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'status' => $isMedia ? 'pending' : 'approved',
                'email_verified_at' => now(),
            ]);

            if ($isMedia) {
                // If media, do not auto-login, redirect to login with verification message
                return redirect()->route('login')
                    ->with('success', 'Your account has been created and is under verification. Please wait for admin approval.');
            }

            // Auto login after registration for staff
            Auth::login($user);

            // Redirect to user dashboard
            return redirect()->route('user.dashboard')
                ->with('success', 'Registration successful! Welcome to HallEase.');
        } catch (\Exception $e) {
            report($e);
            return back()
                ->withInput($request->only('name', 'email', 'phone', 'role'))
                ->with('error', 'An error occurred during registration. Please try again.');
        }
    }
}

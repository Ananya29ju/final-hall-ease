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
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required' => 'Name is required',
            'name.max' => 'Name must not exceed 255 characters',
            'email.required' => 'Email is required',
            'email.email' => 'Please enter a valid email',
            'email.unique' => 'This email is already registered',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 6 characters',
            'password.confirmed' => 'Passwords do not match',
        ]);

        try {
            // Create new user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'role' => 'user', // Default role
                'email_verified_at' => now(),
            ]);

            // Auto login after registration
            Auth::login($user);

            // Redirect to user dashboard
            return redirect()->route('user.dashboard')
                ->with('success', 'Registration successful! Welcome to HallEase.');
        } catch (\Exception $e) {
            report($e);

            return back()
                ->withInput($request->only('name', 'email', 'phone'))
                ->with('error', 'An error occurred during registration. Please try again.');
        }
    }
}

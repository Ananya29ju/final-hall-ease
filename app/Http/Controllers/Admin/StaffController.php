<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * StaffController (Admin)
 * 
 * Manages the creation, role assignment, updating, and deletion of staff, admins,
 * and media personnel. Also enforces strict institutional email validation policies.
 */
class StaffController extends Controller
{
    /**
     * Display a listing of staff
     */
    public function index()
    {
        $admins = User::where('role', 'admin')
            ->latest()
            ->paginate(10, ['*'], 'admins_page');

        $staffMembers = User::where('role', 'user')
            ->latest()
            ->paginate(10, ['*'], 'staff_page');

        // Only show approved media members in the main management list
        $mediaMembers = User::where('role', 'media')
            ->where('status', 'approved')
            ->latest()
            ->paginate(10, ['*'], 'media_page');

        return view('admin.staff.index', compact('admins', 'staffMembers', 'mediaMembers'));
    }

    /**
     * Show the form for creating new staff
     */
    public function create()
    {
        return view('admin.staff.create');
    }

    /**
     * Show the form for creating a new user account.
     */
    public function createUser()
    {
        return view('admin.staff.create-user');
    }

    /**
     * Store a newly created staff member
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'unique:users,email',
                function ($attribute, $value, $fail) {
                    if (!str_ends_with(strtolower($value), '@staloysius.edu.in')) {
                        $fail('Only institutional email addresses ending with @staloysius.edu.in are allowed.');
                    }
                },
            ],
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'status' => 'approved',
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.staff.index')
            ->with('success', 'Admin account added successfully!');
    }

    /**
     * Store a newly created user (or media) account based on administrative input.
     * 
     * Enforces strict validation rules, including a custom closure to ensure the email
     * ends with the `@staloysius.edu.in` domain before proceeding to create the user account.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'unique:users,email',
                function ($attribute, $value, $fail) {
                    if (!str_ends_with(strtolower($value), '@staloysius.edu.in')) {
                        $fail('Only institutional email addresses ending with @staloysius.edu.in are allowed.');
                    }
                },
            ],
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:user,media',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'status' => 'approved',
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.staff.index')
            ->with('success', $this->roleLabel($validated['role']) . ' account created successfully!');
    }

    /**
     * Display the specified staff member
     */
    public function show(User $staff)
    {
        return view('admin.staff.show', compact('staff'));
    }

    /**
     * Show the form for editing the specified staff member
     */
    public function edit(User $staff)
    {
        return view('admin.staff.edit', compact('staff'));
    }

    /**
     * Update the specified staff member's core configuration and roles.
     * 
     * Performs strict email validation to enforce the institutional domain limit. 
     * Furthermore, it checks if the administrator is updating their *own* role, 
     * and updates their active session dashboard route dynamically if they do so.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User $staff
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $staff)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'unique:users,email,' . $staff->id,
                function ($attribute, $value, $fail) {
                    if (!str_ends_with(strtolower($value), '@staloysius.edu.in')) {
                        $fail('Only institutional email addresses ending with @staloysius.edu.in are allowed.');
                    }
                },
            ],
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,user,media',
        ]);

        $staff->update($validated);

        if (Auth::id() === $staff->id) {
            session()->flash('success', 'Your account was updated successfully.');

            return redirect()->route($this->dashboardRouteForRole($staff->role));
        }

        return redirect()
            ->route('admin.staff.index')
            ->with('success', 'Account updated successfully!');
    }

    /**
     * Resolve the dashboard route name for the given role.
     */
    private function dashboardRouteForRole(string $role): string
    {
        return match ($role) {
            'admin' => 'admin.dashboard',
            'media' => 'media.dashboard',
            default => 'user.dashboard',
        };
    }

    /**
     * Resolve a friendly label for the given role.
     */
    private function roleLabel(string $role): string
    {
        return match ($role) {
            'user' => 'Staff',
            'media' => 'Media',
            default => ucfirst($role),
        };
    }

    /**
     * Remove the specified staff member
     */
    public function destroy(User $staff)
    {
        $staff->delete();

        return redirect()
            ->route('admin.staff.index')
            ->with('success', 'Staff member deleted successfully!');
    }
}

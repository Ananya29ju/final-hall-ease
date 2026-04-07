<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * CustomerController (Admin)
 * 
 * Manages operations related to system users (often referred to as 'customers' or 'staff'
 * in different contexts) specifically for administrative oversight.
 */
class CustomerController extends Controller
{
    /**
     * Display a paginated list of all users with the 'customer' role.
     * 
     * Includes a count of bookings associated with each user.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $customers = User::where('role', 'customer')
            ->withCount('bookings')
            ->latest()
            ->paginate(10);

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Display detailed information and booking history for a specific customer.
     * 
     * Aborts with a 404 error if the requested user does not have the 'customer' role.
     *
     * @param \App\Models\User $customer The user to display
     * @return \Illuminate\View\View
     */
    public function show(User $customer)
    {
        if ($customer->role !== 'customer') {
            abort(404);
        }

        $bookings = $customer->bookings()
            ->with('hall')
            ->latest()
            ->paginate(10);

        return view('admin.customers.show', compact('customer', 'bookings'));
    }

    /**
     * Remove the specified customer/staff member from the database.
     * 
     * Checks are applied to ensure only users with the 'customer' role can be deleted
     * and prevents deletion if they currently have active (confirmed) bookings.
     *
     * @param \App\Models\User $customer The user to delete
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $customer)
    {
        if ($customer->role === 'customer') {

            // Optional: Prevent delete if active bookings exist
            if ($customer->bookings()->where('booking_status', 'confirmed')->exists()) {
                return back()->with('error', 'Cannot delete staff with active bookings!');
            }

            $customer->delete();

            return redirect()
                ->route('admin.customers.index')
                ->with('success', 'Staff deleted successfully!');
        }

        return back()->with('error', 'Only staff can be deleted!');
    }
}

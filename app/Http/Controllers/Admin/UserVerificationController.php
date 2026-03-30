<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserVerificationController extends Controller
{
    /**
     * Display a listing of user verification requests.
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $query = User::where('role', 'media');

        if ($status) {
            $query->where('status', $status);
        }

        $users = $query->latest()->paginate(10);
        
        $pendingCount = User::where('role', 'media')->where('status', 'pending')->count();
        $approvedCount = User::where('role', 'media')->where('status', 'approved')->count();
        $rejectedCount = User::where('role', 'media')->where('status', 'rejected')->count();

        return view('admin.verifications.index', [
            'users' => $users,
            'currentStatus' => $status,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
        ]);
    }

    /**
     * Update the user status.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $user->update([
            'status' => $request->status
        ]);

        $action = $request->status === 'approved' ? 'approved' : 'rejected';

        return redirect()->back()->with('success', "Account for {$user->name} has been {$action}.");
    }
}

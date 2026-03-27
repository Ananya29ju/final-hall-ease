<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StaffMiddleware
{
    /**
     * Only allow users with 'user' (Staff) role to access staff/user routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || Auth::user()->isAdmin()) {
            // If admin or guest, don't allow access to staff-specific routes
            // Note: Adhering to "admin can ONLY access the admin routes"
            if (Auth::check() && Auth::user()->isAdmin()) {
                return redirect()->route('admin.dashboard')->with('error', 'Admins are restricted to the admin dashboard.');
            }
            
            abort(403, 'Unauthorized. Staff access only.');
        }

        return $next($request);
    }
}

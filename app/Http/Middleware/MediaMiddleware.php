<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MediaMiddleware
{
    /**
     * Only allow users with the media role to access media routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !Auth::user()->isMedia()) {
            if (Auth::check() && Auth::user()->isAdmin()) {
                return redirect()->route('admin.dashboard')->with('error', 'Admins are restricted to the admin dashboard.');
            }

            if (Auth::check() && Auth::user()->isStaff()) {
                return redirect()->route('user.dashboard')->with('error', 'Staff accounts are restricted to the staff dashboard.');
            }

            abort(403, 'Unauthorized. Media access only.');
        }

        return $next($request);
    }
}

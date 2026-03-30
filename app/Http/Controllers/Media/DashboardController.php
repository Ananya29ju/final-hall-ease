<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Display the media dashboard with recent notifications.
     */
    public function index()
    {
        $notifications = auth()->user()->notifications()->latest()->take(10)->get();
        $unreadCount = auth()->user()->unreadNotifications()->count();

        return view('media.dashboard', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}

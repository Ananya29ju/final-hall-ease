<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    /**
     * Show admin notifications.
     */
    public function index()
    {
        $notifications = auth()->user()->notifications()->latest()->take(30)->get();

        return view('admin.notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark notification as read or unread.
     */
    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();
        
        if ($notification) {
            if ($notification->read()) {
                $notification->markAsUnread();
                $status = 'unread';
            } else {
                $notification->markAsRead();
                $status = 'read';
            }
            return back()->with('success', "Notification marked as {$status}.");
        }

        return back()->with('error', 'Notification not found.');
    }
}

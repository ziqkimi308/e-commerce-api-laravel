<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        // Query all notifications
        $notifications = $request->user()->notifications()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    public function unread(Request $request)
    {
        // Query unread notifications
        $notifications = $request->user()->unreadNotifications()->get();

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'count' => $notifications->count()
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        // Query notification by id
        $notification = $request->user()->notifications()->findOrFail($id);

        // Mark that notification as read
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        // Query mark all unread notifications as read
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    public function destroy(Request $request, $id)
    {
        // Query notification by id
        $notification = $request->user()->notifications->findOrFail($id);

        // Delete
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }
}

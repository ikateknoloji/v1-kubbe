<?php

namespace App\Http\Controllers\API\V1\Order;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = AdminNotification::orderBy('is_read')
                          ->orderBy('created_at', 'desc')
                          ->paginate(10);

        $unreadCount = AdminNotification::where('is_read', 0)->count();
        
        $response = $notifications->toArray();
        $response['unread_count'] = $unreadCount;

        return response()->json($response);
    }
    

    public function markAsRead($id)
    {
        $notification = AdminNotification::findOrFail($id);

        $notification->update([
            'is_read' => true,
            'read_by_user_id' => Auth::id()
        ]);

        return response()->json(['message' => 'Notification marked as read']);
    }

    public function markAllAsRead()
    {
        $notifications = AdminNotification::where('is_read', false)->get();

        foreach ($notifications as $notification) {
            $notification->update([
                'is_read' => true,
                'read_by_user_id' => Auth::id()
            ]);
        }

        return response()->json(['message' => 'All notifications marked as read']);
    }
}

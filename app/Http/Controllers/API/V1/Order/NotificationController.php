<?php

namespace App\Http\Controllers\API\V1\Order;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\CourierNotification;
use App\Models\DesignerNotification;
use App\Models\UserNotification;
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
        /**
     * Get the notifications for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getCustomerNotifications(Request $request)
    {
        $user = Auth::user();

        // Kullanıcının bildirimlerini al ve sırala
        $notifications = UserNotification::where('user_id', $user->id)
            ->orderBy('is_read')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Okunmamış bildirimlerin sayısını hesapla
        $unreadCount = UserNotification::where('user_id', $user->id)
            ->where('is_read', 0)
            ->count();

        $response = $notifications->toArray();

        $response['unread_count'] = $unreadCount;

        return response()->json($response);
    }

    /**
     * Mark a notification as read.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function markAsReadCustomer(Request $request, $id)
    {
        // Bildirimi bul
        $notification = UserNotification::find($id);

        // Bildirimi okundu olarak işaretle
        $notification->is_read = true;
        $notification->save();

        return response()->json(['message' => 'Notification marked as read']);
    }

    /**
    * Get the notifications for the authenticated user.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
   public function getDesingerNotifications(Request $request)
   {
    $notifications = DesignerNotification::orderBy('is_read')
                      ->orderBy('created_at', 'desc')
                      ->paginate(10);

    $unreadCount = DesignerNotification::where('is_read', 0)->count();
    
    $response = $notifications->toArray();
    $response['unread_count'] = $unreadCount;

    return response()->json($response);
   }

   public function markAsReadDesigner(Request $request, $id)
   {
        $notification = DesignerNotification::find($id);

        $notification->update([
            'is_read' => true,
            'read_by_user_id' => Auth::id()
        ]);

        return response()->json(['message' => 'Notification marked as read']);
    }
    public function getCourierNotifications(Request $request)
    {
        $notifications = CourierNotification::orderBy('is_read')
                          ->orderBy('created_at', 'desc')
                          ->paginate(10);
    
        $unreadCount = CourierNotification::where('is_read', 0)->count();
        
        $response = $notifications->toArray();
        $response['unread_count'] = $unreadCount;
    
        return response()->json($response);
    }
    
    public function markAsReadCourier(Request $request, $id)
    {
        // Bildirimi bul
        $notification = CourierNotification::find($id);
    
        // Bildirimi okundu olarak işaretle
        $notification->is_read = true;
        $notification->save();
    
        return response()->json(['message' => 'Notification marked as read']);
    }
}

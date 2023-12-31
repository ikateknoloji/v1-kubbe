<?php

namespace App\Http\Controllers\API\V1\Manage;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GetRejectOrderController extends Controller
{
    /**
     * Admin tarafından reddedilen ('CR' ve 'MR') siparişleri getirir.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAdminRejectedOrders()
    {
        // 'CR' (Customer Rejected) ve 'MR' (Manufacturer Rejected) durumlarına sahip siparişleri ve reject bilgilerini al
        $orders = Order::whereIn('is_rejected', ['R'])
            ->with([
                'rejects' => function ($query) {
                    // İlgili reject bilgilerini getir
                    $query->select('id', 'order_id', 'reason', 'created_at');
                },
            ])
            ->with(['customer' => function ($query) {
                // İlgili müşteri bilgilerini getir
                $query->select('user_id', 'id', 'name', 'surname', 'company_name', 'phone','image_url')
                ->with(['user' => function ($query) {
                    $query->select('id', 'email');
                }]);
            }, 'customerInfo'])
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate();
            
        return response()->json(['orders' => $orders], 200);
    }

    
    public function getAdminCanceledOrders()
    {
        // 'CR' (Customer Rejected) ve 'MR' (Manufacturer Rejected) durumlarına sahip siparişleri ve reject bilgilerini al
        $orders = Order::whereIn('is_rejected', ['C'])
            ->with([
                'rejects' => function ($query) {
                    // İlgili reject bilgilerini getir
                    $query->select('id', 'order_id', 'reason', 'created_at');
                },
            ])
            ->with(['customer' => function ($query) {
                // İlgili müşteri bilgilerini getir
                $query->select('user_id', 'id', 'name', 'surname', 'company_name', 'phone','image_url')
                ->with(['user' => function ($query) {
                    $query->select('id', 'email');
                }]);
            }, 'customerInfo'])
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate();
            
        return response()->json(['orders' => $orders], 200);
    }


    /**
     * Yetkilendirme bilgisi ile gelen kullanıcının reddedilen siparişlerini getirir.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserRejectedOrders(Request $request)
    {
        // Yetkilendirme bilgisi ile gelen kullanıcıyı al
        $user = Auth::user();   

        // 'R' durumuna sahip siparişleri ve reject bilgilerini al
        $orders = Order::where('is_rejected', 'R')
            ->where('customer_id', $user->id) // Sadece yetkilendirme bilgisi ile gelen kullanıcının siparişlerini al
            ->with([
                'rejects' => function ($query) {
                    // İlgili reject bilgilerini getir
                    $query->select('id', 'order_id', 'reason', 'created_at');
                },
            ])
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate();   

        return response()->json(['orders' => $orders], 200);
    }

    /**
      * Yetkilendirme bilgisi ile gelen kullanıcının iptal edilen siparişlerini getirir.
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\JsonResponse
      */
       public function getUserCanceledOrders(Request $request)
    {
         // Yetkilendirme bilgisi ile gelen kullanıcıyı al
         $user = Auth::user(); 

         // 'C' durumuna sahip siparişleri ve reject bilgilerini al
         $orders = Order::where('is_rejected', 'C')
             ->where('customer_id', $user->id) // Sadece yetkilendirme bilgisi ile gelen kullanıcının siparişlerini al
             ->with([
                 'rejects' => function ($query) {
                     // İlgili reject bilgilerini getir
                     $query->select('id', 'order_id', 'reason', 'created_at');
                 },
             ])
             ->with(['customer' => function ($query) {
                // İlgili müşteri bilgilerini getir
                $query->select('user_id', 'id', 'name', 'surname', 'company_name', 'phone','image_url')
                ->with(['user' => function ($query) {
                    $query->select('id', 'email');
                }]);
            }, 'customerInfo'])
             ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
             ->paginate();       

         return response()->json(['orders' => $orders], 200);
     }


    /**
     * 'R' (Rejected) durumuna sahip siparişleri getirir.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRejectedCustomerOrders()
    {
        $customerId = Auth::id();

        // 'R' (Rejected) durumuna sahip siparişleri ve reject bilgilerini al
        $orders = Order::where('is_rejected', 'R')
            ->where('customer_id', $customerId)
            ->with([
                'rejects' => function ($query) {
                    // İlgili reject bilgilerini getir
                    $query->select('id', 'order_id', 'reason', 'created_at');
                },
            ])
            ->with(['customer' => function ($query) {
                // İlgili müşteri bilgilerini getir
                $query->select('user_id', 'id', 'name', 'surname', 'company_name', 'phone','image_url')
                ->with(['user' => function ($query) {
                    $query->select('id', 'email');
                }]);
            }, 'customerInfo'])
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate();

        return response()->json(['orders' => $orders], 200);
    }
    /**
     * 'R' (Rejected) durumuna sahip siparişleri getirir.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRejectedManufacturerOrders()
    {
        $customerId = Auth::id();

        // 'R' (Rejected) durumuna sahip siparişleri ve reject bilgilerini al
        $orders = Order::where('is_rejected', 'R')
            ->where('manufacturer_id', $customerId)
            ->with([
                'rejects' => function ($query) {
                    // İlgili reject bilgilerini getir
                    $query->select('id', 'order_id', 'reason', 'created_at');
                },
            ])
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate();

        return response()->json(['orders' => $orders], 200);
    }

}

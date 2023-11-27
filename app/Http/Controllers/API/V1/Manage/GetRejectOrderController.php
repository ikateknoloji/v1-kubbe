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
        $orders = Order::whereIn('is_rejected', ['CR', 'MR'])
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

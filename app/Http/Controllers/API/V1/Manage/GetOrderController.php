<?php

namespace App\Http\Controllers\API\V1\Manage;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GetOrderController extends Controller
{
    /**
     * Aktif durumda olan ve teslim edilmemiş siparişleri getirir.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveOrders()
    {
        // 'A' (Active) durumuna sahip ve teslim edilmemiş siparişleri al
        $orders = Order::where('is_rejected', 'A')
            ->whereDoesntHave('orderItems', function ($query) {
                $query->where('status', 'PD'); // 'PD' (Ürün Teslim Edildi) durumuna sahip orderItems olmayanları al
            })
            ->with(['customer.user' => function ($query) {
                // İlgili müşteri bilgilerini getir
                $query->select('id', 'name', 'surname', 'company_name', 'email', 'phone');
            }])
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate();

        return response()->json(['orders' => $orders], 200);
    }

    /**
     * Belirtilen 'status' değerine sahip siparişleri getirir.
     *
     * @param  string  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrdersByStatus($status)
    {
        // Belirtilen 'status' değerine sahip siparişleri al
        $orders = Order::where('status', $status)
            ->where('is_rejected', 'A')
            ->with(['customer.user', 'manufacturer.user' => function ($query) {
                // İlgili müşteri ve üretici bilgilerini getir
                $query->select('id', 'name', 'surname', 'company_name', 'email', 'phone');
            }])
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate();

        return response()->json(['orders' => $orders], 200);
    }

    /**
     * Belirtilen müşteri 'id' değerine sahip siparişleri getirir.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomerOrders()
    {
        $customerId = Auth::id();
    
        // Belirtilen müşteri 'id' değerine sahip siparişleri al
        $orders = Order::where('customer_id', $customerId)
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate();
    
        return response()->json(['orders' => $orders], 200);
    }

    /**
     * Belirtilen üretici 'id' değerine sahip siparişleri getirir.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getManufacturerOrders()
    {
        $manufacturerId = Auth::id();

        // Belirtilen üretici 'id' değerine sahip siparişleri al
        $orders = Order::where('manufacturer_id', $manufacturerId)
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate();

        return response()->json(['orders' => $orders], 200);
    }

    /**
     * Belirtilen 'id' değerine sahip tekil siparişi getirir.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderById($id)
    {
        // Belirtilen 'id' değerine sahip tekil siparişi al
        $order = Order::with([
            'customer.user',
            'manufacturer', // sadece manufacturer ilişkisini belirtiyoruz
            'orderItems.productType.productCategory',
            'orderImages',
            'rejects',
            'orderCancellation',
        ])->find($id);
    
        if (!$order) {
            return response()->json(['message' => 'Sipariş bulunamadı.'], 404);
        }
    
        return response()->json(['order' => $order], 200);
    }

    /**
     * Belirtilen müşteri 'id' değerine sahip ve belirtilen 'status' değerine sahip siparişleri getirir.
     *
     * @param  string  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomerOrdersByStatus($status)
    {
        $customerId = Auth::id();   

        // Belirtilen müşteri 'id' değerine sahip ve belirtilen 'status' değerine sahip siparişleri al
        $orders = Order::where('customer_id', $customerId)
            ->where('status', $status)
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate();   

        return response()->json(['orders' => $orders], 200);
    }   

    /**
     * Belirtilen üretici 'id' değerine sahip ve belirtilen 'status' değerine sahip siparişleri getirir.
     *
     * @param  string  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function getManufacturerOrdersByStatus($status)
    {
        $manufacturerId = Auth::id();   

        // Belirtilen üretici 'id' değerine sahip ve belirtilen 'status' değerine sahip siparişleri al
        $orders = Order::where('manufacturer_id', $manufacturerId)
            ->where('status', $status)
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate();   

        return response()->json(['orders' => $orders], 200);
    }


}

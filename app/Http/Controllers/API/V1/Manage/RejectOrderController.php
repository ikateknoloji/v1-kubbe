<?php

namespace App\Http\Controllers\API\V1\Manage;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderCancellation;
use App\Models\Reject;
use Illuminate\Http\Request;

class RejectOrderController extends Controller
{
    /**
     * Admin tarafından bir siparişi reddeder.
     *
     * @param  int  $orderId
     * @param  string  $reason
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminRejectOrder($orderId, $reason)
    {
        // Siparişi bul
        $order = Order::find($orderId);
    
        if (!$order) {
            return response()->json(['message' => 'Sipariş bulunamadı.'], 404);
        }
    
        // Siparişi reddet ve gerekli alanları güncelle
        $order->is_rejected = 'CR'; // 'CR' değeri admin tarafından reddedildiği anlamına gelir
        $order->save();
    
        // Reddetme nedenini ve diğer detayları içeren bir 'reject' kaydı oluştur
        $reject = new Reject([
            'reason' => $reason,
        ]);
    
        $order->rejects()->save($reject);
    
        return response()->json(['message' => 'Sipariş admin tarafından reddedildi.'], 200);
    }

    /**
     * Müşteri tarafından bir siparişi reddeder.
     *
     * @param  int  $orderId
     * @param  string  $reason
     * @return \Illuminate\Http\JsonResponse
     */
    public function customerRejectOrder($orderId, $reason)
    {
        // Siparişi bul
        $order = Order::find($orderId);
    
        if (!$order) {
            return response()->json(['message' => 'Sipariş bulunamadı.'], 404);
        }
    
        // Siparişi reddet ve gerekli alanları güncelle
        $order->is_rejected = 'MR'; // 'MR' değeri müşteri tarafından reddedildiği anlamına gelir
        $order->save();
    
        // Reddetme nedenini ve diğer detayları içeren bir 'reject' kaydı oluştur
        $reject = new Reject([
            'reason' => $reason,
        ]);
    
        $order->rejects()->save($reject);
    
        return response()->json(['message' => 'Sipariş müşteri tarafından reddedildi.'], 200);
    }
    
    /**
     * Üretici tarafından bir siparişi reddeder.
     *
     * @param  int  $orderId
     * @param  string  $reason
     * @return \Illuminate\Http\JsonResponse
     */
    public function manufacturerRejectOrder($orderId, $reason)
    {
        // Siparişi bul
        $order = Order::find($orderId);
    
        if (!$order) {
            return response()->json(['message' => 'Sipariş bulunamadı.'], 404);
        }
    
        // Siparişi reddet ve gerekli alanları güncelle
        $order->is_rejected = 'R'; // 'R' değeri üretici tarafından reddedildiği anlamına gelir
        $order->save();
    
        // Reddetme nedenini ve diğer detayları içeren bir 'reject' kaydı oluştur
        $reject = new Reject([
            'reason' => $reason,
        ]);
    
        $order->rejects()->save($reject);
    
        return response()->json(['message' => 'Sipariş üretici tarafından reddedildi.'], 200);
    }
    
    /**
     * Bir sipariş iptal talebi oluşturur.
     *
     * @param  int  $orderId
     * @param  string  $reason
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelOrderRequest($orderId, $reason)
    {
        // Siparişi bul
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Sipariş bulunamadı.'], 404);
        }

        // Siparişi iptal et ve gerekli alanları güncelle
        $order->status = 'ORC'; // 'ORC' değeri siparişin iptal edildiği anlamına gelir
        $order->save();

        // İptal nedenini ve diğer detayları içeren bir 'orderCancellation' kaydı oluştur
        $orderCancellation = new OrderCancellation([
            'reason' => $reason,
            'approved' => false, // İptal talebi henüz onaylanmamış
        ]);

        $order->orderCancellation()->save($orderCancellation);

        return response()->json(['message' => 'Sipariş iptal edildi ve onay bekliyor.'], 200);
    }

    /**
     * Bir siparişi iptal eder.
     *
     * @param  int  $orderId
     * @param  string  $reason
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelOrder($orderId, $reason)
    {
        // Siparişi bul
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Sipariş bulunamadı.'], 404);
        }

        // Siparişi iptal et ve gerekli alanları güncelle
        $order->status = 'C'; // 'C' değeri siparişin iptal edildiği anlamına gelir
        $order->save();

        // İptal nedenini ve diğer detayları içeren bir 'orderCancellation' kaydı oluştur
        $orderCancellation = new OrderCancellation([
            'reason' => $reason,
            'approved' => true, // İptal talebi onaylandı
        ]);

        $order->orderCancellation()->save($orderCancellation);

        return response()->json(['message' => 'Sipariş iptal edildi.'], 200);
    }

}

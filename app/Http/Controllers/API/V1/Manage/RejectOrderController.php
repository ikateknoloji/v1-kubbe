<?php

namespace App\Http\Controllers\API\V1\Manage;

use App\Events\AdminNotificationEvent;
use App\Events\CustomerNotificationEvent;
use App\Events\OrderStatusChangedEvent;
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
     * @param  Request  $request
     * @param  int  $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminRejectOrder(Request $request, $orderId)
    {
        $request->validate([
            'reason' => 'required|string',
        ], [
            'reason.required' => 'Neden alanı gereklidir.',
            'reason.string' => 'Neden alanı bir metin olmalıdır.',
        ]);

        $reason = $request->input('reason');

        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Sipariş bulunamadı.'], 404);
        }

        $order->is_rejected = 'R';
        $order->save();

        $reject = new Reject([
            'reason' => $reason,
        ]);
        $shortReason = substr($reason, 0, 30) . (strlen($reason) > 30 ? '...' : '');

        $order->rejects()->save($reject);
        // Müşteriye bildirim gönder
        broadcast(new CustomerNotificationEvent($order->customer_id, [
            'title' => 'Sipariş Reddedildi',
            'body' => 'Siparişiniz admin tarafından reddedildi. Sebep: ' . $shortReason,
            'order' => $order,
        ]));
        
        return response()->json(['message' => 'Sipariş admin tarafından reddedildi.'], 200);
    }

    /**
     * Müşteri tarafından bir siparişi reddeder.
     * ? customer
     * @param  Request  $request
     * @param  int  $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function customerRejectOrder(Request $request, $orderId)
    {
        $request->validate([
            'reason' => 'required|string',
        ], [
            'reason.required' => 'Neden alanı gereklidir.',
            'reason.string' => 'Neden alanı bir metin olmalıdır.',
        ]);

        $reason = $request->input('reason');

        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Sipariş bulunamadı.'], 404);
        }

        $order->is_rejected = 'MR';
        $order->save();

        $reject = new Reject([
            'reason' => $reason,
        ]);

        $order->rejects()->save($reject);

            // Adminlere bildirim gönder
        broadcast(new AdminNotificationEvent([
            'title' => 'Sipariş Reddedildi',
            'body' => 'Admin tarafından bir sipariş reddedildi. Sipariş numarası: ' . $order->order_code,
            'order' => $order,
        ]));
        
        return response()->json(['message' => 'Sipariş müşteri tarafından reddedildi.'], 200);
    }

    /**
     * Üretici tarafından bir siparişi reddeder.
     * ? manufacturer
     * @param  Request  $request
     * @param  int  $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function manufacturerRejectOrder(Request $request, $orderId)
    {
        $request->validate([
            'reason' => 'required|string',
        ], [
            'reason.required' => 'Neden alanı gereklidir.',
            'reason.string' => 'Neden alanı bir metin olmalıdır.',
        ]);

        $reason = $request->input('reason');

        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Sipariş bulunamadı.'], 404);
        }

        $order->is_rejected = 'R';
        $order->save();

        $reject = new Reject([
            'reason' => $reason,
        ]);

        $order->rejects()->save($reject);

        // Adminlere bildirim gönder
        broadcast(new AdminNotificationEvent([
            'title' => 'Sipariş Reddedildi',
            'body' => 'Üretici tarafından bir sipariş reddedildi. Sipariş numarası: ' . $order->order_code,
            'order' => $order,
        ]));

        return response()->json(['message' => 'Sipariş üretici tarafından reddedildi.'], 200);
    }

    /**
     * Bir sipariş iptal talebi oluşturur.
     * ? customer
     * @param  Request  $request
     * @param  int  $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelOrderRequest(Request $request, $orderId)
    {
        $request->validate([
            'reason' => 'required|string',
        ], [
            'reason.required' => 'Neden alanı gereklidir.',
            'reason.string' => 'Neden alanı bir metin olmalıdır.',
        ]);

        $reason = $request->input('reason');

        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Sipariş bulunamadı.'], 404);
        }

        $order->status = 'ORC';
        $order->save();

        $orderCancellation = new OrderCancellation([
            'reason' => $reason,
            'approved' => false,
        ]);

        $order->orderCancellation()->save($orderCancellation);

        // Adminlere bildirim gönder
        broadcast(new AdminNotificationEvent([
            'title' => 'Sipariş İptal Talebi Oluşturuldu',
            'body' => 'Müşteri tarafından bir sipariş iptal talebi oluşturuldu. Sipariş numarası: ' . $order->order_code,
            'order' => $order,
        ]));

        return response()->json(['message' => 'Sipariş iptal edildi ve onay bekliyor.'], 200);
    }

    /**
     * Bir siparişi iptal eder.
     * ? admin
     * @param  Request  $request
     * @param  int  $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelOrder(Request $request, $orderId)
    {

        $order = Order::find($orderId);

    
            $order->is_rejected = 'C';
            $order->save();
    
            // OrderStatusChangedEvent olayını yayınla
            event(new OrderStatusChangedEvent($order,[
                'title' => 'Sipariş İptal Edildi',
                'body' => 'Sipariş ' . $order->status . ' durumundaki sipariş iptal edildi.',
                'order' => $order,
            ]));
    
            return response()->json(['message' => 'Sipariş İptal durumuna getirildi ve ilişkili reddetme bilgileri korundu.'], 200);
       
    }

    public function activateOrder($orderId)
    {
        $order = Order::find($orderId);
    
        if ($order->is_rejected == 'R' ||  $order->is_rejected == 'C' ) {
            // İlişkili Reject modelini bul ve sil
            $reject = $order->rejects;
            if ($reject) {
                $reject->delete();
            }
    
            $order->is_rejected = 'A';
            $order->save();
    
            // Müşteriye bildirim gönder
            broadcast(new CustomerNotificationEvent($order->customer_id, [
                'title' => 'Sipariş Aktif Edildi',
                'body' => 'Siparişiniz admin tarafından aktif edildi.',
                'order' => $order,
            ]));
    
            return response()->json(['message' => 'Sipariş aktif durumuna getirildi ve ilişkili reddetme bilgileri silindi.'], 200);
        } else {
            return response()->json(['message' => 'Bu sipariş zaten aktif durumda.'], 400);
        }
    }
}

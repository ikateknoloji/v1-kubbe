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
     * @param  Request  $request
     * @param  int  $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminRejectOrder(Request $request, $orderId)
    {
        $request->validate([
            'reason' => 'required|string',
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
        $request->validate([
            'reason' => 'required|string',
        ]);

        $reason = $request->input('reason');

        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Sipariş bulunamadı.'], 404);
        }

        $order->status = 'C';
        $order->save();

        $orderCancellation = new OrderCancellation([
            'reason' => $reason,
            'approved' => true,
        ]);

        $order->orderCancellation()->save($orderCancellation);

        return response()->json(['message' => 'Sipariş iptal edildi.'], 200);
    }
}

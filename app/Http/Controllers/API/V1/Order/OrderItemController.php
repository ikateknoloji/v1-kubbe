<?php

namespace App\Http\Controllers\API\V1\Order;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Yeni bir kaynak oluşturur ve bu kaynağı depolama alanına ekler.
     */
    public function store(Request $request)
    {
        // Gelen verileri doğrula
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_type_id' => 'required|exists:product_types,id',
            'quantity' => 'required|integer|min:1',
            'color' => 'required|string',
        ]);

        try {
            // Transaksiyon başlat
            DB::beginTransaction();

            // Yeni sipariş öğesi oluştur
            $orderItem = OrderItem::create([
                'order_id' => $request->input('order_id'),
                'product_type_id' => $request->input('product_type_id'),
                'quantity' => $request->input('quantity'),
                'color' => $request->input('color'),
            ]);

            // Transaksiyonu tamamla
            DB::commit();

            // Başarılı oluşturma yanıtı
            return response()->json(['order_item' => $orderItem], 201);
        } catch (\Exception $e) {
            // Hata durumunda transaksiyonu geri al
            DB::rollback();

            // Hata yanıtı
            return response()->json(['error' => 'İşlem sırasında bir hata oluştu.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(OrderItem $orderItem)
    {
        //
    }

    /**
     * Depolama alanındaki belirli bir kaynağı günceller.
     */
    public function update(Request $request, OrderItem $orderItem)
    {
        // Gelen verileri doğrula
        $request->validate([
            'product_type_id' => 'sometimes|required|exists:product_types,id',
            'quantity' => 'sometimes|required|integer|min:1',
            'color' => 'sometimes|required|string',
        ]);

        try {
            // Transaksiyon başlat
            DB::beginTransaction();

            // Veritabanında kaynak bulunamazsa hata döndür
            if (!$orderItem) {
                return response()->json(['error' => 'Belirtilen sipariş öğesi bulunamadı.'], 404);
            }

            // Gelen istek verilerini kullanarak kaynağı güncelle
            $orderItem->update($request->only(['product_type_id', 'quantity', 'color']));

            // Transaksiyonu tamamla
            DB::commit();

            // Başarılı güncelleme yanıtı
            return response()->json(['order_item' => $orderItem], 200);
        } catch (\Exception $e) {
            // Hata durumunda transaksiyonu geri al
            DB::rollback();

            // Hata yanıtı
            return response()->json(['error' => 'İşlem sırasında bir hata oluştu.'], 500);
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrderItem $orderItem)
    {
        try {
            // Transaksiyon başlat
            DB::beginTransaction();

            // Veritabanında kaynak bulunamazsa hata döndür
            if (!$orderItem) {
                return response()->json(['error' => 'Belirtilen sipariş öğesi bulunamadı.'], 404);
            }

            // Sipariş öğesini sil
            $orderItem->delete();

            // Transaksiyonu tamamla
            DB::commit();

            // Başarılı silme yanıtı
            return response()->json(['message' => 'Sipariş öğesi başarıyla silindi'], 200);
        } catch (\Exception $e) {
            // Hata durumunda transaksiyonu geri al
            DB::rollback();

            // Hata yanıtı
            return response()->json(['error' => 'İşlem sırasında bir hata oluştu.'], 500);
        }
    }

}

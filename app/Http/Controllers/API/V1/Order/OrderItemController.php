<?php

namespace App\Http\Controllers\API\V1\Order;

use App\Events\OrderStatusChangedEvent;
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
        try {
            // Gelen verileri doğrula
            $validatedData = $request->validate([
                'order_id' => 'required|exists:orders,id',
                'product_type_id' => 'required|exists:product_types,id',
                'quantity' => 'required|integer|min:1',
                'color' => 'required|string',
            ], [
                'order_id.required' => 'Sipariş ID zorunludur.',
                'order_id.exists' => 'Geçersiz sipariş ID.',
                'product_type_id.required' => 'Ürün tipi zorunludur.',
                'product_type_id.exists' => 'Geçersiz ürün tipi.',
                'quantity.required' => 'Miktar zorunludur.',
                'quantity.integer' => 'Miktar bir sayı olmalıdır.',
                'quantity.min' => 'Miktar en az 1 olmalıdır.',
                'color.required' => 'Renk zorunludur.',
                'color.string' => 'Renk bir metin olmalıdır.',
            ]);
        
            // Transaksiyon başlat
            DB::beginTransaction();

        // Yeni sipariş öğesi oluştur
        $orderItem = OrderItem::create([
            'order_id' => $validatedData['order_id'],
            'product_type_id' => $validatedData['product_type_id'],
                'quantity' => $validatedData['quantity'],
                'color' => $validatedData['color'],
            ]);
        
            // Event'i hemen broadcast et
            broadcast(new OrderStatusChangedEvent(
                        $orderItem->order, [
                        'title' => 'Sipariş üzerinde eklemeler gerçekleşti',
                        'body' => 'Bir sipariş öğesi oluşturuldu.',
                        'order' => $orderItem->order,
                    ]));
        
            // Transaksiyonu tamamla
            DB::commit();
        
            // Başarılı oluşturma yanıtı
            return response()->json(['order_item' => $orderItem], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
                DB::rollback();
                return response()->json(['errors' => $e->errors()], 422);
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
        try {
            // Gelen verileri doğrula
            $request->validate([
                'product_type_id' => 'sometimes|required|exists:product_types,id',
                'quantity' => 'sometimes|required|integer|min:1',
                'color' => 'sometimes|required|string',
            ], [
                'product_type_id.required' => 'Ürün tipi  zorunludur.',
                'product_type_id.exists' => 'Geçersiz ürün tipi .',
                'quantity.required' => 'Miktar zorunludur.',
                'quantity.integer' => 'Miktar bir sayı olmalıdır.',
                'quantity.min' => 'Miktar en az 1 olmalıdır.',
                'color.required' => 'Renk zorunludur.',
                'color.string' => 'Renk bir metin olmalıdır.',
            ]);

            // Transaksiyon başlat
            DB::beginTransaction();

            // Veritabanında kaynak bulunamazsa hata döndür
            if (!$orderItem) {
                return response()->json(['error' => 'Belirtilen sipariş öğesi bulunamadı.'], 404);
            }

            // Gelen istek verilerini kullanarak kaynağı güncelle
            $orderItem->update($request->only(['product_type_id', 'quantity', 'color']));

            // Event'i hemen broadcast et
            broadcast(new OrderStatusChangedEvent(
                $orderItem->order,[
                'title' => 'Sipariş Güncellendi', 
                'body' => 'Sipariş içeriği  güncellendi.', 
                'order' => $orderItem->order
                ]
            ));
 
            // Transaksiyonu tamamla
            DB::commit();

            // Başarılı güncelleme yanıtı
            return response()->json(['order_item' => $orderItem], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json(['errors' => $e->errors()], 422);
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

            // Event'i hemen broadcast et
            broadcast(new OrderStatusChangedEvent(
                $orderItem->order,[
                'title' => 'Sipariş kalemi üzerinde silinme işlemi gerçekleşti.', 
                'body' => 'Bir sipariş öğesi silindi.', 
                'order' => $orderItem->order
                ]
            ));

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

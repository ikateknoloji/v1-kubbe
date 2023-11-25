<?php

namespace App\Http\Controllers\API\V1\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderImage;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Gelen verileri doğrula
        $request->validate([
            'invoice_type' => 'required|in:I,C',
            'offer_price' => 'required|numeric|min:0',
            'order_items' => 'required|array',
            'order_items.*.product_type_id' => 'required|exists:product_types,id',
            'order_items.*.quantity' => 'required|integer|min:1',
            'order_items.*.color' => 'required|string',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            // Transaksiyon başlat
            DB::beginTransaction();
    
            // Yeni sipariş oluştur
            $order = Order::create([
                'customer_id' => Auth::id(),
                'order_code' => Str::random(8), // 8 karakterlik random bir değer
                'status' => 'OC', // Otomatik olarak "OC" durumu
                'invoice_type' => $request->input('invoice_type'),
                'offer_price' => $request->input('offer_price'),
            ]);
    
            // Sipariş öğelerini ekleyerek kaydet
            $orderItems = collect($request->input('order_items'))->map(function ($item) use ($order) {
                return new OrderItem([
                    'order_id' => $order->id,
                    'product_type_id' => $item['product_type_id'],
                    'quantity' => $item['quantity'],
                    'color' => $item['color'],
                ]);
            });
    
            $order->orderItems()->saveMany($orderItems);
    
            // Sipariş resmini ekleyerek kaydet (eğer varsa)
            if ($request->hasFile('image_url')) {
                $image = $request->file('image_url');
                // Resim tipine göre ön ek belirle (örneğin, 'logo_')

                // Resim dosyasına ön ek ekle
                $imageName = 'L' . $order->id . '.' . $image->getClientOriginalExtension();

                $path = $image->storeAs('public/images/orders', $imageName);
            
                // OrderImage modeline order_id'yi ekleyerek kaydet
                $orderImage = new OrderImage([
                    'type' => 'L', // Logo tipi
                    'image_url' => asset(Storage::url($path)),
                    'path' => $path,
                    'order_id' => $order->id,
                ]);
            
                $order->orderImages()->save($orderImage);
            }

            // Transaksiyonu tamamla
            DB::commit();
    
            // Başarılı oluşturma yanıtı
            return response()->json(['order' => $order], 201);
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
    public function show(Order $order)
    {
        //
    }

    /**
     * Belirtilen kaynağı depoda günceller.
     */
    public function update(Request $request, Order $order)
    {
        // Gelen verileri doğrula
        $request->validate([
            'status' => 'sometimes|required|in:OC,DP,DA,P,PA,MS,MA,PP,PR,PD,PIT',
            'manufacturer_id' => 'nullable|sometimes|required|exists:manufacturers,user_id',
            'offer_price' => 'sometimes|required|numeric|min:0',
            'invoice_type' => 'sometimes|required|in:I,C',
            'is_rejected' => 'sometimes|required|in:A,R,C,CR,MR',
        ]);
    
        try {
            // Transaksiyon başlat
            DB::beginTransaction();
    
            // Siparişi güncelle
            $order->update($request->only([
                'status', 'manufacturer_id', 'offer_price', 'invoice_type', 'is_rejected'
            ]));
    
            // Transaksiyonu tamamla
            DB::commit();
    
            // Başarılı güncelleme yanıtı
            return response()->json(['order' => $order], 200);
        } catch (\Exception $e) {
            // Hata durumunda transaksiyonu geri al
            DB::rollback();
    
            // Hata yanıtı
            return response()->json(['error' => 'İşlem sırasında bir hata oluştu.'], 500);
        }
    }    

    /**
     * Belirtilen kaynağı depodan kaldırır (silme işlemi).
     */
    public function destroy(Order $order)
    {
        try {
            // Veritabanı işlemlerini transaksiyon içinde gerçekleştir
            DB::beginTransaction();
    
            // İlgili orderImages tablosundaki resimleri sil
            $order->orderImages->each(function ($image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            });
    
            // İlgili orderItems tablosundaki kayıtları sil
            $order->orderItems->each->delete();
    
            // İlgili orderCancellation tablosundaki kayıtları sil
            $order->orderCancellation->each->delete();
    
            // İlgili orderRejection tablosundaki kayıtları sil
            $order->orderRejections->each->delete();
    
            // İlgili order tablosundaki kaydı sil
            $order->delete();
    
            // İşlem başarılı ise commit yap
            DB::commit();
    
            return response()->json(['message' => 'Sipariş başarıyla silindi'], 200);
        } catch (\Exception $e) {
            // Hata durumunda rollback yap
            DB::rollback();
    
            // Hata yanıtını döndür
            return response()->json(['error' => 'İstek işlenirken bir hata oluştu. ' . $e], 500);
        }
    }
}

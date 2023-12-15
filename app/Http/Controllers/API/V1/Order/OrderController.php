<?php

namespace App\Http\Controllers\API\V1\Order;

use App\Events\OrderStatusChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderImage;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        try {
            // Gelen verileri doğrula
            $request->validate([
                'invoice_type' => 'required|in:I,C',
                'offer_price' => 'required|numeric|min:0',
                'order_items' => 'required|array',
                'order_items.*.product_type_id' => 'required|exists:product_types,id',
                'order_items.*.quantity' => 'required|integer|min:1',
                'order_items.*.color' => 'required|string',
                'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'invoice_type.required' => 'Fatura tipi zorunludur.',
                'invoice_type.in' => 'Geçersiz fatura tipi.',
                'offer_price.required' => 'Teklif fiyatı zorunludur.',
                'offer_price.numeric' => 'Teklif fiyatı bir sayı olmalıdır.',
                'offer_price.min' => 'Teklif fiyatı en az 0 olmalıdır.',
                'order_items.required' => 'Sipariş öğeleri zorunludur.',
                'order_items.array' => 'Sipariş öğeleri bir dizi olmalıdır.',
                'order_items.*.product_type_id.required' => 'Her sipariş öğesi için ürün tipi ID zorunludur.',
                'order_items.*.product_type_id.exists' => 'Geçersiz ürün tipi ID.',
                'order_items.*.quantity.required' => 'Her sipariş öğesi için miktar zorunludur.',
                'order_items.*.quantity.integer' => 'Miktar bir tam sayı olmalıdır.',
                'order_items.*.quantity.min' => 'Miktar en az 1 olmalıdır.',
                'order_items.*.color.required' => 'Her sipariş öğesi için renk zorunludur.',
                'order_items.*.color.string' => 'Renk bir dize olmalıdır.',
                'image_url.image' => 'Geçersiz resim formatı.',
                'image_url.mimes' => 'Geçersiz resim MIME türü.',
                'image_url.max' => 'Resim boyutu en fazla 2048 KB olmalıdır.',
            ]);

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
            
                // MIME tipini al
                $mime_type = $image->getClientMimeType();

                // OrderImage modeline order_id'yi ekleyerek kaydet
                $orderImage = new OrderImage([
                    'type' => 'L', // Logo tipi
                    'image_url' => asset(Storage::url($path)),
                    'path' => $path,
                    'order_id' => $order->id,
                    'mime_type' => $mime_type, // MIME tipini kaydet
                ]);
            
                $order->orderImages()->save($orderImage);
            }

            broadcast(new OrderStatusChangedEvent($order, [
                'title' => 'Yeni Sipariş Oluşturuldu',
                'body' => 'Bir sipariş oluşturuldu.',
                'order' => $order,
            ]));
            
            // Transaksiyonu tamamla
            DB::commit();
            

    
            // Başarılı oluşturma yanıtı
            return response()->json(['order' => $order], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json(['errors' => $e->errors()], 422);
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
        try {
        // Gelen verileri doğrula
        $request->validate([
            'status' => 'sometimes|required|in:OC,DP,DA,P,PA,MS,MA,PP,PR,PD,PIT',
            'manufacturer_id' => 'nullable|sometimes|required|exists:manufacturers,user_id',
            'offer_price' => 'sometimes|required|numeric|min:0',
            'invoice_type' => 'sometimes|required|in:I,C',
            'is_rejected' => 'sometimes|required|in:A,R,C,CR,MR',
        ], [
            'status.required' => 'Durum zorunludur.',
            'status.in' => 'Geçersiz durum.',
            'manufacturer_id.exists' => 'Geçersiz üretici ID.',
            'offer_price.required' => 'Teklif fiyatı zorunludur.',
            'offer_price.numeric' => 'Teklif fiyatı bir sayı olmalıdır.',
            'offer_price.min' => 'Teklif fiyatı en az 0 olmalıdır.',
            'invoice_type.required' => 'Fatura tipi zorunludur.',
            'invoice_type.in' => 'Geçersiz fatura tipi.',
            'is_rejected.required' => 'Red durumu zorunludur.',
            'is_rejected.in' => 'Geçersiz red durumu.',
        ]);

        // Transaksiyon başlat
        DB::beginTransaction();

        // Siparişi güncelle
        $order->update($request->only([
            'status', 'manufacturer_id', 'offer_price', 'invoice_type', 'is_rejected'
        ]));

        // Event'i hemen broadcast et
        broadcast(new OrderStatusChangedEvent($order, [
                'title' => 'Sipariş Güncellendi',
                'body' => 'Sipariş durumu güncellendi.',
                'order' => $order->toArray(),
            ]));

            // Transaksiyonu tamamla
            DB::commit();

            // Başarılı güncelleme yanıtı
            return response()->json(['order' => $order], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json(['errors' => $e->errors()], 422);
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
    
            broadcast(new OrderStatusChangedEvent($order, [
                'title' => 'Sipariş Silindi',
                'body' => 'Bir sipariş silindi.',
                'order' => $order->toArray(),
            ]));

            // İşlem başarılı ise commit yap
            DB::commit();
    

            return response()->json(['message' => 'Sipariş başarıyla silindi'], 200);
            
        } catch (\Exception $e) {

            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
        
    }



}

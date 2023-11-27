<?php

namespace App\Http\Controllers\API\V1\Manage;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderManageController extends Controller
{

    /**
     * Sipariş Durumunu Tasarım Aşamasına Geçir.
     */
    public function transitionToDesignPhase(Order $order)
    {
        // Sipariş durumunu kontrol et, sadece 'OC' durumundakileri güncelle
        if ($order->status === 'OC') {
            // Sipariş durumunu 'DP' (Tasarım Aşaması) olarak güncelle
            $order->update(['status' => 'DP']);

            return response()->json(['message' => 'Sipariş tasarım aşamasına geçirildi.'], 200);
        }

        return response()->json(['error' => 'Sipariş durumu ' . $order->status . ' olduğu için güncellenemiyor.'], 400);
    }



    /**
    * Tasarımı Onayla ve Resmi Kaydet.
    */
    public function approveDesign(Request $request, Order $order)
    {
        // Sipariş durumunu kontrol et, sadece 'DP' durumundakileri güncelle
        if ($order->status === 'DP') {
            // Gelen resim dosyasını kontrol et
            $request->validate([
                'design_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
        
            // Resim dosyasını yükle ve bilgileri al
            $image = $request->file('design_image');
            $imageName = 'design_' . $order->id . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('public/images/designs', $imageName);
        
            // OrderImage modeline order_id'yi ekleyerek kaydet
            $orderImage = new OrderImage([
                'type' => 'D', // Tasarım tipi
                'image_url' => asset(Storage::url($path)),
                'path' => $path,
                'order_id' => $order->id,
            ]);
        
            $order->orderImages()->save($orderImage);
        
            // Sipariş durumunu 'DA' (Onay) olarak güncelle
            $order->update(['status' => 'DA']);
        
            return response()->json(['message' => 'Tasarım onaylandı ve kaydedildi.'], 200);
        }
    
        return response()->json(['error' => 'Sipariş durumu ' . $order->status . ' olduğu için tasarım onayı verilemiyor.'], 400);
    }
    

    /**
     * Tasarım Onayını ve Ödemeyi Gerçekleştir.
     */
    public function approvePaymentAndProceed(Request $request, Order $order)
    {
        // Sipariş durumunu kontrol et, sadece 'DA' durumundakileri güncelle
        if ($order->status === 'DA') {
            // Gelen resim dosyasını kontrol et
            $request->validate([
                'payment_proof' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            // Resim dosyasını yükle ve bilgileri al
            $image = $request->file('payment_proof');
            $imageName = 'payment_' . $order->id . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('public/images/payments', $imageName);

            // OrderImage modeline order_id'yi ekleyerek kaydet
            $orderImage = new OrderImage([
                'type' => 'P', // Ödeme tipi
                'image_url' => asset(Storage::url($path)),
                'path' => $path,
                'order_id' => $order->id,
            ]);

            $order->orderImages()->save($orderImage);

            // Sipariş durumunu 'P' (Ödeme Onayı) olarak güncelle
            $order->update(['status' => 'P']);

            return response()->json(['message' => 'Ödeme onaylandı ve sipariş aşamasına geçildi.'], 200);
        }

        return response()->json(['error' => 'Sipariş durumu ' . $order->status . ' olduğu için işlem gerçekleştirilemiyor.'], 400);
    }

    /**
     * Ödemeyi Doğrula.
     */
    public function verifyPayment(Order $order)
    {
        // Sipariş durumunu kontrol et, sadece 'P' durumundakileri doğrula
        if ($order->status === 'P') {
            // Ödeme durumunu 'PA' (Ödeme Onaylandı) olarak güncelle
            $order->update(['status' => 'PA']);

            return response()->json(['message' => 'Ödeme doğrulandı.'], 200);
        }

        return response()->json(['error' => 'Sipariş durumu ' . $order->status . ' olduğu için ödeme doğrulanamıyor.'], 400);
    }

    /**
     * Üretici Seçimi İşlemini Gerçekleştir.
     */
    public function selectManufacturer(Request $request, Order $order)
    {
        // Sipariş durumunu kontrol et, sadece 'PA' durumundakileri işle
        if ($order->status === 'PA') {
            // Gelen üretici bilgilerini kontrol et
            $request->validate([
                'manufacturer_id' => 'required|exists:manufacturers,user_id',
            ]);

            // Üreticiyi seç ve sipariş durumunu 'MS' (Üretici Seçimi) olarak güncelle
            $order->update([
                'manufacturer_id' => $request->input('manufacturer_id'),
                'status' => 'MS',
            ]);

            return response()->json(['message' => 'Üretici seçimi yapıldı.'], 200);
        }

        return response()->json(['error' => 'Sipariş durumu ' . $order->status . ' olduğu için üretici seçimi yapılamıyor.'], 400);
    }

    /**
     * Ürünün hazır olduğunu belirtir ve resim yükler.
     */
    public function markProductReady(Request $request, Order $order)
    {
        // Sipariş durumunu kontrol et, sadece 'PP' durumundakileri güncelle
        if ($order->status === 'PP') {
            // Gelen resim dosyasını kontrol et
            $request->validate([
                'product_ready_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
        
            // Resim dosyasını yükle ve bilgileri al
            $image = $request->file('product_ready_image');
            $imageName = 'product_ready_' . $order->id . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('public/images/product_ready', $imageName);
        
            // OrderImage modeline order_id'yi ekleyerek kaydet
            $orderImage = new OrderImage([
                'type' => 'PR', // Product Ready tipi
                'image_url' => asset(Storage::url($path)),
                'path' => $path,
                'order_id' => $order->id,
            ]);
        
            $order->orderImages()->save($orderImage);
        
            // Sipariş durumunu 'PR' (Product Ready) olarak güncelle
            $order->update(['status' => 'PR']);
        
            return response()->json(['message' => 'Ürün hazırlandı ve kaydedildi.'], 200);
        }

        return response()->json(['error' => 'Sipariş durumu ' . $order->status . ' olduğu için ürün hazırlandı olarak işaretlenemiyor.'], 400);
    }

    /**
     * Ürünün kargo aşamasında olduğunu belirtir ve resim ekler.
     */
    public function markProductInTransition(Request $request, Order $order)
    {
        // Sipariş durumunu kontrol et, sadece 'PR' durumundakileri güncelle
        if ($order->status === 'PR') {
            // Gelen resim dosyasını kontrol et
            $request->validate([
                'product_in_transition_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
        
            // Resim dosyasını yükle ve bilgileri al
            $image = $request->file('product_in_transition_image');
            $imageName = 'product_in_transition_' . $order->id . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('public/images/product_in_transition', $imageName);
        
            // OrderImage modeline order_id'yi ekleyerek kaydet
            $orderImage = new OrderImage([
                'type' => 'SC', // Product in Transition tipi
                'image_url' => asset(Storage::url($path)),
                'path' => $path,
                'order_id' => $order->id,
            ]);
        
            $order->orderImages()->save($orderImage);
        
            // Sipariş durumunu 'PIT' (Product in Transition) olarak güncelle
            $order->update(['status' => 'PIT']);
        
            return response()->json(['message' => 'Ürün geçiş aşamasında ve resim eklendi.'], 200);
        }

        return response()->json(['error' => 'Sipariş durumu ' . $order->status . ' olduğu için ürün geçiş aşamasına işaretlenemiyor.'], 400);
    }

    /**
     * Ürünün teslim edildiğini belirtir.
     */
    public function markProductDelivered(Order $order)
    {
        // Sipariş durumunu kontrol et, sadece 'PIT' durumundakileri güncelle
        if ($order->status === 'PIT') {
            // Sipariş durumunu 'PD' (Product Delivered) olarak güncelle
            $order->update(['status' => 'PD']); 

            return response()->json(['message' => 'Ürün teslim edildi.'], 200);
        }   

        return response()->json(['error' => 'Sipariş durumu ' . $order->status . ' olduğu için ürün teslim edilemiyor.'], 400);
    }

}

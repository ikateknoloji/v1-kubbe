<?php

namespace App\Http\Controllers\API\V1\Manage;

use App\Events\AdminNotificationEvent;
use App\Events\CustomerNotificationEvent;
use App\Events\ManufacturerNotificationEvent;
use App\Events\UserNotificationEvent;
use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\OrderImage;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderManageController extends Controller
{

    /**
     * Sipariş Durumunu Tasarım Aşamasına Geçir.
     * ? admin rotası
     */
    public function transitionToDesignPhase(Order $order)
    {
        // Sipariş durumunu kontrol et, sadece 'OC' durumundakileri güncelle
        if ($order->status === 'Sipariş Onayı') {
            // Sipariş durumunu 'DP' (Tasarım Aşaması) olarak güncelle
            $order->update(['status' => 'DP']);

            // Müşteriye bildirim gönder
            broadcast(new CustomerNotificationEvent($order->customer_id, [
                'title' => 'Sipariş Durumu Değişti',
                'body' => 'Sipariş tasarım aşamasına geçirildi.',
                'order' => $order->toArray(),
            ]));

            return response()->json(['message' => 'Sipariş tasarım aşamasına geçirildi.'], 200);
        }

        return response()->json(['error' => 'Sipariş durumu ' . $order->status . ' olduğu için güncellenemiyor.'], 400);
    }



    /**
    * Tasarım ekle ve Resmi Kaydet.
    *? admin rotası
    */
    public function approveDesign(Request $request, Order $order)
    {
        try {

            // Gelen resim dosyasını kontrol et
            $request->validate([
                'design_image' => 'required|file|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048',
            ], [
                'design_image.required' => 'Lütfen bir tasarım resmi yükleyin.',
                'design_image.image' => 'Dosya bir resim olmalıdır.',
                'design_image.mimes' => 'Dosya formatı jpeg, png, jpg, gif veya svg olmalıdır.',
                'design_image.max' => 'Dosya boyutu maksimum 2048 kilobayt olmalıdır.',
            ]);

            // Sipariş durumunu kontrol et, sadece 'DP' durumundakileri güncelle
            if ($order->status === 'Tasarım Aşaması') {

            
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

                // Müşteriye bildirim gönder
                broadcast(new CustomerNotificationEvent($order->customer_id, [
                    'title' => 'Tasarım Onaylandı',
                    'body' => 'Sipariş tasarımı onaylandı ve kaydedildi.',
                    'order' => $order->toArray(),
                ]));

                return response()->json(['message' => 'Tasarım onaylandı ve kaydedildi.'], 200);
            }

            return response()->json(['error' => 'Sipariş durumu ' . $order->status . ' olduğu için tasarım onayı verilemiyor.'], 400);

        }  catch (\Illuminate\Validation\ValidationException $e) {
            $firstErrorMessage = head($e->validator->errors()->all());

            DB::rollback();
            return response()->json(['error' => $firstErrorMessage], 422);
        }
    
    }
    

    /**
     * Tasarım Onayını ve Ödemeyi Gerçekleştir.
     * ? customer
     */
    public function approvePaymentAndProceed(Request $request, Order $order)
    {
        // Sipariş durumunu kontrol et, sadece 'DA' durumundakileri güncelle
        if ($order->status === 'Tasarım Onaylandı') {

            $request->validate([
                'payment_proof' => 'required|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048',
            ], [
                'payment_proof.required' => 'Ödeme kanıtı dosyası gereklidir.',
                'payment_proof.mimes' => 'Dosya formatı jpeg, png, jpg, gif, svg veya pdf olmalıdır.',
                'payment_proof.max' => 'Dosya boyutu maksimum 2048 kilobayt olmalıdır.',
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
                'order_id' => $order->id, // toArray() kullanılmasına gerek yok
            ]);

            $order->orderImages()->save($orderImage);

            // Sipariş durumunu 'P' (Ödeme Onayı) olarak güncelle
            $order->update(['status' => 'P']);

                    // Admin'e bildirim gönder
            broadcast(new AdminNotificationEvent([
                'title' => 'Ödeme Onaylandı',
                'body' => 'Sipariş ödemesi yapıldı lütfen kontrol edip onaylayın.',
                'order' => $order,
            ]));

            return response()->json(['message' => 'Ödeme onaylandı ve sipariş aşamasına geçildi.'], 200);
        }

        return response()->json(['error' => 'Sipariş durumu ' . $order->status . ' olduğu için işlem gerçekleştirilemiyor.'], 400);
    }

    /**
     * Ödemeyi Doğrula.
     * ? admin
     */
    public function verifyPayment(Order $order)
    {
        // Sipariş durumunu kontrol et, sadece 'P' durumundakileri doğrula
        if ($order->status === 'Ödeme Aşaması') {
            // Ödeme durumunu 'PA' (Ödeme Onaylandı) olarak güncelle
            $order->update(['status' => 'PA']);

            // Admin'e bildirim gönder
            broadcast(new CustomerNotificationEvent($order->customer_id ,[
                'title' => 'Ödeme Onaylandı',
                'body' => 'Sipariş ödemesi onaylandı ve sipariş aşamasına geçildi.',
                'order' => $order->toArray(),
            ]));


            return response()->json(['message' => 'Ödeme doğrulandı.'], 200);
        }

        return response()->json(['error' => 'Sipariş durumu ' . $order->status . ' olduğu için ödeme doğrulanamıyor.'], 400);
    }

    /**
     * Üretici Seçimi İşlemini Gerçekleştir.
     * ? admin
     */
    public function selectManufacturer(Request $request, Order $order)
    {
        // Sipariş durumunu kontrol et, sadece 'PA' durumundakileri işle
        if ($order->status === 'Ödeme Alındı') {
            // Gelen üretici bilgilerini kontrol et
            $request->validate([
                'manufacturer_id' => 'required|exists:manufacturers,user_id',
            ]);

            // Üreticiyi seç ve sipariş durumunu 'MS' (Üretici Seçimi) olarak güncelle
            $order->update([
                'manufacturer_id' => $request->input('manufacturer_id'),
                'status' => 'MS',
            ]);
            
            broadcast(new ManufacturerNotificationEvent (
                $request->input('manufacturer_id') ,[
                'title' => 'Yeni Sipariş',
                'body' => 'Yeni bir sipariş isteği var teklifi incele',
                'order' => $order->toArray(),
            ]));

            return response()->json(['message' => 'Üretici seçimi yapıldı.'], 200);
        }

        return response()->json(['error' => 'Sipariş durumu ' . $order->status . ' olduğu için üretici seçimi yapılamıyor.'], 400);
    }

    /**
     * Üreticinin şiparişi kabul etmesi.
     * ? manufacturer
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmManufacturer(Request $request, Order $order)
    {
        // Giriş yapan kullanıcının üretici olup olmadığını kontrol et
        $manufacturerId = Auth::id();

        // Order'ın manufacturer_id'si ile giriş yapan üreticinin user_id'sini kontrol et
        if ($order->manufacturer_id != $manufacturerId) {
            return response()->json(['error' => 'Bu işlemi sadece ilgili üretici gerçekleştirebilir.'], 403);
        }

        // Sipariş durumunu kontrol et, sadece 'PA' durumundakileri işle
        if ($order->status === 'Üretici Seçimi') {
            // Üreticiyi onayla ve sipariş durumunu 'MA' (Üretici Onayı) olarak güncelle
            $order->update([
                'status' => 'MA',
            ]);

            broadcast(new AdminNotificationEvent([
                'title' => 'Sipariş onayı',
                'body' => 'Sipariş üretici tarafından onaylandı.',
                'order' => $order->toArray(),
            ]));
        
            return response()->json(['message' => 'Üretici onayı yapıldı.'], 200);
        }

        return response()->json(['error' => 'Sipariş durumu ' . $order->status . ' olduğu için üretici onayı yapılamıyor.'], 400);
    }

    /**
     * Üretici onayından sonra üretim sürecini başlat.
     * ? manufacturer
     */
    public function startProduction(Request $request, Order $order)
    {
        // Giriş yapan kullanıcının üretici olup olmadığını kontrol et
        $manufacturerId = Auth::id();   

        // Order'ın manufacturer_id'si ile giriş yapan üreticinin user_id'sini kontrol et
        if ($order->manufacturer_id != $manufacturerId) {
            return response()->json(['error' => 'Bu işlemi sadece ilgili üretici gerçekleştirebilir.'], 403);
        }   

        // Sipariş durumunu kontrol et, sadece 'MA' (Üretici Onayı) durumundakileri güncelle
        if ($order->status === 'Üretici Onayı') {
            // Sipariş durumunu 'PP' (Üretimde) olarak güncelle
            $order->update(['status' => 'PP']); 
                
            // Üreticiye bildirim gönder
            event(new CustomerNotificationEvent($order->customer_id ,[
                'title' => 'Üretim Süreci Başlatıldı',
                'body' => 'Sipariş numarası ' . $order->order_code . ' için üretim süreci başlatıldı.',
                'order' => $order,
            ]));

            // Üreticiye bildirim gönder
            event(new AdminNotificationEvent([
                'title' => 'Üretim Süreci Başlatıldı',
                'body' => 'Sipariş numarası ' . $order->order_code . ' için üretim süreci başlatıldı.',
                'order' => $order->toArray(),
            ]));

            return response()->json(['message' => 'Üretim süreci başlatıldı.'], 200);
        }   

        return response()->json(['error' => 'Sipariş durumu ' . $order->status . ' olduğu için üretim süreci başlatılamıyor.'], 400);
    }

    /**
     * Ürünün hazır olduğunu belirtir ve resim yükler.
     * ? manufacturer
     */
    public function markProductReady(Request $request, Order $order)
    {
        // Sipariş durumunu kontrol et, sadece 'PP' durumundakileri güncelle
        if ($order->status === 'Üretimde') {
            // Gelen resim dosyasını kontrol et
            $request->validate([
                'product_ready_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'product_ready_image.required' => 'Ürün hazır resmi gereklidir.',
                'product_ready_image.image' => 'Dosya bir resim olmalıdır.',
                'product_ready_image.mimes' => 'Dosya formatı jpeg, png, jpg, gif veya svg olmalıdır.',
                'product_ready_image.max' => 'Dosya boyutu maksimum 2048 kilobayt olmalıdır.',
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
        
            // Üreticiye bildirim gönder
            event(new CustomerNotificationEvent($order->customer_id ,[
                'title' => 'Üretim Süreci Başlatıldı',
                'body' => 'Sipariş numarası ' . $order->order_code . ' için ürün hazır hale getirildi.',
                'order' => $order->toArray(),
            ]));

            // Üreticiye bildirim gönder
            event(new AdminNotificationEvent([
                'title' => 'Ürün Hazır',
                'body' => 'Sipariş numarası' . $order->order_code . ' için ürün hazır hale getirildi.',
                'order' => $order->toArray(),
            ]));            

            return response()->json(['message' => 'Ürün hazırlandı ve kaydedildi.'], 200);
        }

        return response()->json(['error' => 'Sipariş durumu ' . $order->status . ' olduğu için ürün hazırlandı olarak işaretlenemiyor.'], 400);
    }

    /**
     * Ürünün kargo aşamasında olduğunu belirtir ve resim ekler.
     * ? admin
     */
    public function markProductInTransition(Request $request, Order $order)
    {
        // Sipariş durumunu kontrol et, sadece 'PR' durumundakileri güncelle
        if ($order->status === 'Ürün Hazır') {
            // Gelen resim dosyasını kontrol et
            $request->validate([
                'product_in_transition_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'product_in_transition_image.required' => 'Ürün geçiş resmi gereklidir.',
                'product_in_transition_image.image' => 'Dosya bir resim olmalıdır.',
                'product_in_transition_image.mimes' => 'Dosya formatı jpeg, png, jpg, gif veya svg olmalıdır.',
                'product_in_transition_image.max' => 'Dosya boyutu maksimum 2048 kilobayt olmalıdır.',
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

            // Üreticiye bildirim gönder
            event(new CustomerNotificationEvent($order->customer_id ,[
                'title' => 'Ürün Kargoda',
                'body' => 'Sipariş numarası ' . $order->order_code . ' için ürün kargo aşamasına alındı.',
                'order' => $order->toArray(),
            ]));

            return response()->json(['message' => 'Ürün geçiş aşamasında ve resim eklendi.'], 200);
        }

        return response()->json(['error' => 'Sipariş durumu ' . $order->status . ' olduğu için ürün geçiş aşamasına işaretlenemiyor.'], 400);
    }

    /**
     * Ürünün teslim edildiğini belirtir.
     * ? admin customer 
     */
    public function markProductDelivered(Order $order)
    {
        // Sipariş durumunu kontrol et, sadece 'PIT' durumundakileri güncelle
        if ($order->status === 'Ürün Transfer Aşaması') {
            // Sipariş durumunu 'PD' (Product Delivered) olarak güncelle
            $order->update(['status' => 'PD']); 

            // Üreticiye bildirim gönder
            event(new CustomerNotificationEvent($order->customer_id ,[
                'title' => 'Sipariş Teslim Edildi',
                'body' => 'Sipariş numarası ' . $order->order_code . ' teslim edildi.',
                'order' => $order->toArray(),
            ]));

            return response()->json(['message' => 'Ürün teslim edildi.'], 200);
        }   

        return response()->json(['error' => 'Sipariş durumu ' . $order->status . ' olduğu için ürün teslim edilemiyor.'], 400);
    }

    /**
     * Fatura ekler ve müşteriye bildirim gönderir.
     * ? admin rotası
     */
    public function addInvoice(Request $request, Order $order)
    {
        // Gelen fatura dosyasını kontrol et
        $request->validate([
            'invoice_file' => 'required|mimes:pdf|max:2048',
        ], [
            'invoice_file.required' => 'Fatura dosyası gereklidir.',
            'invoice_file.mimes' => 'Dosya formatı sadece PDF olmalıdır.',
            'invoice_file.max' => 'Dosya boyutu maksimum 2048 kilobayt olmalıdır.',
        ]);
    
        // Fatura dosyasını yükle ve bilgileri al
        $invoiceFile = $request->file('invoice_file');
        $invoiceFileName = 'invoice_' . $order->id . '.' . $invoiceFile->getClientOriginalExtension();
        $invoicePath = $invoiceFile->storeAs('public/invoices', $invoiceFileName);
    
        // Fatura bilgilerini OrderImage modeline kaydet
        $orderImage = new OrderImage([
            'type' => 'I', // Fatura tipi
            'image_url' => asset(Storage::url($invoicePath)),
            'path' => $invoicePath,
            'order_id' => $order->id,
        ]);
    
        $order->orderImages()->save($orderImage);

        // Müşteriye bildirim gönder
        event(new CustomerNotificationEvent($order->customer_id,[
            'title' => 'Fatura Eklendi',
            'body' => 'Sipariş numaranız ' . $order->id . ' için fatura eklendi.',
            'order' => $order->toArray(),
        ]));
    
        return response()->json(['message' => 'Fatura eklendi ve müşteriye bildirim gönderildi.'], 200);
    }


}

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
use Illuminate\Support\Arr;

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
                'design_image' => 'required|file|mimes:jpeg,png,jpg,gif,svg,pdf',
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
            
                // MIME tipini al
                $mime_type = $image->getClientMimeType();
                
                // OrderImage modeline order_id'yi ekleyerek kaydet
                $orderImage = new OrderImage([
                'type' => 'D', // Tasarım tipi
                'image_url' => asset(Storage::url($path)),
                'path' => $path,
                'mime_type' => $mime_type, // MIME tipini kaydet
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
        if ($order->status === 'Tasarım Eklendi') {

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

            // MIME tipini al
            $mime_type = $image->getClientMimeType();

            // OrderImage modeline order_id'yi ekleyerek kaydet
            $orderImage = new OrderImage([
                'type' => 'P', // Ödeme tipi
                'image_url' => asset(Storage::url($path)),
                'path' => $path,
                'mime_type' => $mime_type, // MIME tipini kaydet
                'order_id' => $order->id,
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
                'order_logo' => 'required|mimes:jpeg,png,jpg,gif,svg,pdf',
            ], 
            [
                'order_logo.required' => 'Logo çıktısı dosyası gereklidir.',
                'order_logo.mimes' => 'Dosya formatı jpeg, png, jpg, gif, svg veya pdf olmalıdır.',
                'order_logo.max' => 'Dosya boyutu maksimum 2048 kilobayt olmalıdır.',
            ]);

            // Resim dosyasını yükle ve bilgileri al
            $image = $request->file('order_logo');
            $imageName = 'order_logo' . $order->id . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('public/images/order_logo', $imageName);

            // MIME tipini al
            $mime_type = $image->getClientMimeType();

            // OrderImage modeline order_id'yi ekleyerek kaydet
            $orderImage = new OrderImage([
                'type' => 'LP', // Ödeme tipi
                'image_url' => asset(Storage::url($path)),
                'path' => $path,
                'mime_type' => $mime_type, // MIME tipini kaydet
                'order_id' => $order->id,
            ]);

            $order->orderImages()->save($orderImage);

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

        // Sipariş durumunu kontrol et, sadece 'OA' (Teklifi Onayı) durumundakileri güncelle
        if ($order->status === 'Üretici Seçimi') {
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
                'product_ready_image' => 'required|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
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
        
            // MIME tipini al
            $mime_type = $image->getClientMimeType();
            
            // OrderImage modeline order_id'yi ekleyerek kaydet
            $orderImage = new OrderImage([
                'type' => 'PR', // Product Ready tipi
                'image_url' => asset(Storage::url($path)),
                'path' => $path,
                'mime_type' => $mime_type, // MIME tipini kaydet
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
        
            // MIME tipini al
            $mime_type = $image->getClientMimeType();
            
            // OrderImage modeline order_id'yi ekleyerek kaydet
            $orderImage = new OrderImage([
                'type' => 'SC', // Product in Transition tipi
                'image_url' => asset(Storage::url($path)),
                'path' => $path,
                'mime_type' => $mime_type, // MIME tipini kaydet
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
    
        // MIME tipini al
        $mime_type = $invoiceFile->getClientMimeType();
        
        // Fatura bilgilerini OrderImage modeline kaydet
        $orderImage = new OrderImage([
            'type' => 'I', // Fatura tipi
            'image_url' => asset(Storage::url($invoicePath)),
            'path' => $invoicePath,
            'mime_type' => $mime_type, // MIME tipini kaydet
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

    /**
     * Form içeriklerinin validation işlemlerini yapıyoruz.
     * ? Tüm rotalar rotası
     */
    public function validateForms(Request $request)
    {
        // Fatura tipine göre doğrulama kurallarını belirle
        $rules = [
        'order_name' => 'required|string',
        'invoice_type' => 'required|in:I,C',
        'note' => 'nullable|string',
        'image_url' => 'required|file|mimes:jpeg,png,jpg,gif,svg,pdf',
        'name' => 'required|string',
        'surname' => 'required|string',
        'phone' => 'required|string|regex:/^([5]{1}[0-9]{9})$/',
        'email' => 'nullable|email',
        ];

        if ($request->input('invoice_type') == 'C') {
        $rules['company_name'] = 'required|string';
        $rules['address'] = 'required|string';
        $rules['tax_office'] = 'required|string';
        $rules['tax_number'] = 'required|string';
        $rules['email'] = 'required|email';
        }

        $request->validate($rules,[
            'order_name' => 'Şipariş adı gereklidir',
            'invoice_type.required' => 'Fatura tipi zorunludur.',
            'invoice_type.in' => 'Geçersiz fatura tipi.',
            'image_url.image' => 'Geçersiz resim formatı.',
            'image_url.mimes' => 'Geçersiz resim MIME türü.',
            'image_url.max' => 'Resim boyutu en fazla 2048 KB olmalıdır.',
            'phone.required' => 'Telefon numarası zorunludur.',
            'phone.string' => 'Telefon numarası bir dize olmalıdır.',
            'phone.regex' => 'Geçersiz telefon numarası.',
            'name.required' => 'Ad alanı zorunludur.',
            'name.string' => 'Ad alanı bir dize olmalıdır.',
            'surname.required' => 'Soyadı alanı zorunludur.',
            'surname.string' => 'Soyadı alanı bir dize olmalıdır.',
            'company_name.required' => 'Şirket adı alanı zorunludur.',
            'company_name.string' => 'Şirket adı bir dize olmalıdır.',
            'address.required' => 'Adres alanı zorunludur.',
            'address.string' => 'Adres bir dize olmalıdır.',
            'tax_office.required' => 'Vergi dairesi alanı zorunludur.',
            'tax_office.string' => 'Vergi dairesi bir dize olmalıdır.',
            'tax_number.required' => 'Vergi numarası alanı zorunludur.',
            'tax_number.string' => 'Vergi numarası bir dize olmalıdır.',
            'email.required' => 'E-posta alanı zorunludur.',
            'email.email' => 'Geçersiz e-posta adresi.',
        ]);


    
        // Doğrulama başarılı
        return response()->json(['message' => 'Doğrulama başarılı'], 200);
    }
    
    public function validateOrderItem(Request $request)
    {
        try {
            // Validate incoming data
            $validatedData = $request->validate([
                'product_type_id' => 'nullable|exists:product_types,id',
                'type' => 'nullable|string',
                'product_category_id' => 'required|exists:product_categories,id',
                'quantity' => 'required|integer|min:1',
                'color' => 'required|string',
                'unit_price' => 'required|numeric|min:0',
            ], [
                'product_type_id.exists' => 'Geçersiz ürün tipi.',
                'product_category_id.required' => 'Ürün kategorisi gereklidir.',
                'product_category_id.exists' => 'Geçersiz ürün kategorisi.',
                'quantity.required' => 'Miktar gereklidir.',
                'quantity.integer' => 'Miktar bir tam sayı olmalıdır.',
                'quantity.min' => 'Miktar en az 1 olmalıdır.',
                'color.required' => 'Renk gereklidir.',
                'color.string' => 'Renk bir metin olmalıdır.',
                'unit_price.required' => 'Birim fiyat gereklidir.',
                'unit_price.numeric' => 'Birim fiyat bir sayı olmalıdır.',
                'unit_price.min' => 'Birim fiyat en az 0 olmalıdır.',
            ]);

            if (empty($request->input('product_type_id')) && empty($request->input('type'))) {
                return response()->json(['error' => 'Ürün tipi zorunludur.'], 422);
            }
    
            // Successful validation response
            return response()->json(['message' => 'Doğrulama başarılı.'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Get the first error message
            $firstError = Arr::first($e->errors())[0];
    
            // Return error response
            return response()->json(['error' => $firstError], 422);
        }
    }
    
    
}

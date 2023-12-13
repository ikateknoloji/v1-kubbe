<?php

namespace App\Http\Controllers\API\V1\Manage;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GetOrderController extends Controller
{
    /**
     * Aktif durumda olan ve teslim edilmemiş siparişleri getirir.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveOrders()
    {
        
        // 'A' (Active) durumuna sahip ve teslim edilmemiş siparişleri al
        $orders = Order::where('is_rejected', 'A')
            ->whereDoesntHave('orderItems', function ($query) {
                $query->where('status', 'PD'); // 'PD' (Ürün Teslim Edildi) durumuna sahip orderItems olmayanları al
            })
            ->with(['customer' => function ($query) {
                // İlgili müşteri bilgilerini getir
                $query->select('user_id', 'id', 'name', 'surname', 'company_name', 'phone')
                ->with(['user' => function ($query) {
                    $query->select('id', 'email');
                }]);
            }])
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate();


        return response()->json(['orders' => $orders], 200);
    }


    /**
     * Belirtilen 'status' değerine sahip siparişleri getirir.
     *
     * @param  string  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrdersByStatus($status)
    {
        // Belirtilen 'status' değerine sahip siparişleri al
        $orders = Order::where('status', $status)
            ->where('is_rejected', 'A')
            ->with(['customer' => function ($query) {
                // İlgili müşteri ve üretici bilgilerini getir
                $query->select('user_id','id', 'name', 'surname', 'company_name', 'phone')
                ->with(['user' => function ($query) {
                    $query->select('id', 'email');
                }]);
            }
            , 'manufacturer' => function ($query) {
                // İlgili müşteri ve üretici bilgilerini getir
                $query->select('user_id','id', 'name', 'surname', 'company_name', 'phone')
                ->with(['user' => function ($query) {
                    $query->select('id', 'email');
                }]);
            }])
        ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
        ->paginate(10);


        return response()->json(['orders' => $orders], 200);
    }

    /**
     * Belirtilen müşteri 'id' değerine sahip siparişleri getirir.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomerOrders()
    {
        $customerId = Auth::id();
    
        // Belirtilen müşteri 'id' değerine sahip siparişleri al
        $orders = Order::where('customer_id', $customerId)
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate();
    
        return response()->json(['orders' => $orders], 200);
    }

    /**
     * Belirtilen üretici 'id' değerine sahip siparişleri getirir.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getManufacturerOrders()
    {
        $manufacturerId = Auth::id();

        // Belirtilen üretici 'id' değerine sahip siparişleri al
        $orders = Order::where('manufacturer_id', $manufacturerId)
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate();

        return response()->json(['orders' => $orders], 200);
    }

    /**
     * Belirtilen 'id' değerine sahip tekil siparişi getirir.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderById($id)
    {
        $order = Order::with([
            'customer.user',
            'manufacturer',
            'orderItems.productType.productCategory',
            'orderImages',
            'rejects',
            'orderCancellation',
        ])->find($id);
        
        // İlgili resim tiplerini filtreleme
        $filteredImages = $order->orderImages
            ->whereIn('type', ['L', 'D'])
            ->groupBy('type')
            ->map(function ($images) {
                return $images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'order_id' => $image->order_id,
                        'type' => $image->type,
                        'image_url' => $image->image_url,
                        'path' => $image->path,
                        'created_at' => $image->created_at,
                        'updated_at' => $image->updated_at,
                    ];
                })->first(); // Sadece ilk resmi al
            });
        
        // Dönüştürülmüş resimleri, sipariş nesnesine ekleyin
        $order->formatted_order_images = $filteredImages->toArray();
        
        // Dönüştürülmüş sipariş nesnesini kullanabilirsiniz
        return response()->json(['order' => $order], 200);        
        
    }

    /**
     * Belirtilen müşteri 'id' değerine sahip ve belirtilen 'status' değerine sahip siparişleri getirir.
     *
     * @param  string  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomerOrdersByStatus($status)
    {
        $customerId = Auth::id();   

        // Belirtilen müşteri 'id' değerine sahip ve belirtilen 'status' değerine sahip siparişleri al
        $orders = Order::where('customer_id', $customerId)
            ->where('status', $status)
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate();   

        return response()->json(['orders' => $orders], 200);
    }   

    /**
     * Belirtilen üretici 'id' değerine sahip ve belirtilen 'status' değerine sahip siparişleri getirir.
     *
     * @param  string  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function getManufacturerOrdersByStatus($status)
    {
        $manufacturerId = Auth::id();   

        // Belirtilen üretici 'id' değerine sahip ve belirtilen 'status' değerine sahip siparişleri al
        $orders = Order::where('manufacturer_id', $manufacturerId)
            ->where('status', $status)
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate();   

        return response()->json(['orders' => $orders], 200);
    }
    /*
    public function downloadImage($imageId)
    {
            // Resim bulma işlemi
            $orderImage = OrderImage::find($imageId);
    
            if (!$orderImage) {
                return response()->json(['error' => 'Resim bulunamadı.'], 404);
            }

            // Dosyanın tam yolu
            $filePath = storage_path("app/{$orderImage->path}");

            // Dosya adını al
            $fileName = basename($filePath);
    
            // İndirme işlemi
            return response()->download($filePath, $fileName);
    }
    */
    public function downloadImage($imageId)
    {
        // Veritabanından ilgili imageId'ye sahip dosya bilgisini al
        $orderImage = OrderImage::findOrFail($imageId);
    
        // Dosyanın yolunu oluştur
        $pathToFile = storage_path('app/' . $orderImage->path);
    
        // Dosyanın gerçek medya türünü belirle
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $contentType = finfo_file($finfo, $pathToFile);
        finfo_close($finfo);
    
        // Dosya adını belirle (orijinal adıyla)
        $fileName = pathinfo($pathToFile)['basename'];
    
        // Dosyayı tarayıcıya gönder
        return response()->file($pathToFile, [
            'Content-Type' => $contentType, 
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"'
        ]);
    }
}

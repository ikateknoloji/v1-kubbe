<?php

namespace App\Http\Controllers\API\V1\Manage;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GetOrderController extends Controller
{
    /**
     * Aktif durumda olan ve teslim edilmemiş siparişleri getirir.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveOrders()
    {
        
        $oneWeekAgo = \Carbon\Carbon::now()->subWeek();

        // 'A' (Active) durumuna sahip ve teslim edilmemiş siparişleri al
        $orders = Order::where('is_rejected', 'A')
            ->where('created_at', '>=', $oneWeekAgo)
            ->whereDoesntHave('orderItems', function ($query) {
                $query->where('status', 'PD'); // 'PD' (Ürün Teslim Edildi) durumuna sahip orderItems olmayanları al
            })
            ->with(['customer' => function ($query) {
                // İlgili müşteri bilgilerini getir
                $query->select('user_id', 'id', 'name', 'surname', 'company_name', 'phone','image_url')
                ->with(['user' => function ($query) {
                    $query->select('id', 'email');
                }]);
            }, 'customerInfo']) // customerInfo ilişkisini ekledik
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate(12);
    
        return response()->json(['orders' => $orders], 200);
    }
    

    public function getOldestOrders()
    {
        // Belirli durumları içeren en eski siparişleri al
        $statuses = ['OC', 'P', 'MO', 'PR'];

        // Her durum için en eski 5 siparişi içeren bir dizi oluştur
        $oldestOrdersByStatus = [];
        foreach ($statuses as $status) {
            $oldestOrdersByStatus[$status] = Order::where('status', $status)
                ->orderBy('updated_at', 'asc')
                ->take(4)
                ->get();

            // Her bir durumdaki siparişler için müşteri bilgilerini yükle
            $oldestOrdersByStatus[$status]->load('customer');
        }

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
        ->paginate(5);

        // Daha fazla işlem veya döndürme adımları eklenebilir
        return response()->json(['oldest_orders' => $oldestOrdersByStatus , 'orders' =>  $orders ]);
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
                $query->select('user_id', 'id', 'name', 'surname', 'company_name', 'phone','image_url')
                ->with(['user' => function ($query) {
                    $query->select('id', 'email');
                }]);
            }, 'manufacturer' => function ($query) {
                // İlgili müşteri ve üretici bilgilerini getir
                $query->select('user_id','id', 'name', 'surname', 'company_name', 'phone')
                ->with(['user' => function ($query) {
                    $query->select('id', 'email');
                }]);
            }, 'customerInfo'])// customerInfo ilişkisini ekledik
            ->with([
                'rejects' => function ($query) {
                    // İlgili reject bilgilerini getir
                    $query->select('id', 'order_id', 'reason', 'created_at');
                },
            ]) 
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate(9);
    
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
            ->with('customerInfo') // customerInfo ilişkisini ekledik
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate(6);
    
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
            ->paginate(6);

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
            'manufacturer.user',
            'orderItems.productType',
            'orderItems.productCategory',
            'orderImages',
            'rejects',
            'orderCancellation',
            'customerInfo',
            'invoiceInfo',
            'orderAddress'
        ])->find($id);
    
        // Siparişin admin tarafından okunduğunu belirt
        if (!$order->admin_read) {
            $order->admin_read = true;
            $order->save();
        }
    
        // İlgili resim tiplerini filtreleme
        $filteredImages = $order->orderImages
            ->whereIn('type', ['L', 'D','P','PR','SC','PL'])
            ->groupBy('type')
            ->map(function ($images) {
                return $images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'order_id' => $image->order_id,
                        'type' => $image->type,
                        'image_url' => $image->image_url,
                        'path' => $image->path,
                        'mime_type' => $image->mime_type,
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
     * Belirtilen 'id' değerine sahip tekil siparişi getirir.
     * Ancak, siparişin 'customer_id' değeri, Auth bilgileri ile aynı olmalıdır.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderByIdForCustomer($id)
    {
        // Auth bilgilerini al
        $user = Auth::user();
    
        $order = Order::with([
            'customer.user',
            'manufacturer.user',
            'orderItems.productType',
            'orderItems.productCategory',
            'orderImages',
            'rejects',
            'orderCancellation',
            'customerInfo',
            'invoiceInfo',
            'orderAddress'
        ])->find($id);
    
        // Siparişin 'customer_id' değeri, Auth bilgileri ile aynı olmalıdır
        if ($order->customer->user->id != $user->id) {
            return response()->json(['error' => 'Bu siparişi görüntüleme yetkiniz yok.'], 403);
        }
    
        // Siparişin müşteri tarafından okunduğunu belirt
        if (!$order->customer_read) {
            $order->customer_read = true;
            $order->save();
        }
    
        // İlgili resim tiplerini filtreleme
        $filteredImages = $order->orderImages
            ->whereIn('type', ['L', 'D','P','PR','SC','PL'])
            ->groupBy('type')
            ->map(function ($images) {
                return $images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'order_id' => $image->order_id,
                        'type' => $image->type,
                        'image_url' => $image->image_url,
                        'path' => $image->path,
                        'mime_type' => $image->mime_type,
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
    * Belirtilen 'id' değerine sahip tekil siparişi getirir.
    * Ancak, siparişin 'manufacturer_id' değeri, Auth bilgileri ile aynı olmalıdır.
    *
    * @param  int  $id
    * @return \Illuminate\Http\JsonResponse
    */
    public function getOrderByIdForManufacturer($id)
    {
        // Auth bilgilerini al
        $user = Auth::user();
    
        $order = Order::with([
            'customer.user',
            'manufacturer.user',
            'orderItems.productType',
            'orderItems.productCategory',
            'orderImages',
            'rejects',
        ])->find($id);
    
        // Siparişin 'manufacturer_id' değeri, Auth bilgileri ile aynı olmalıdır
        if ($order->manufacturer->user->id != $user->id) {
            return response()->json(['error' => 'Bu siparişi görüntüleme yetkiniz yok.'], 403);
        }
    
        // Siparişin üretici tarafından okunduğunu belirt
        if (!$order->manufacturer_read) {
            $order->manufacturer_read = true;
            $order->save();
        }
    
        // İlgili resim tiplerini filtreleme
        $filteredImages = $order->orderImages
            ->whereIn('type', ['L','PR','PL'])
            ->groupBy('type')
            ->map(function ($images) {
                return $images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'order_id' => $image->order_id,
                        'type' => $image->type,
                        'image_url' => $image->image_url,
                        'path' => $image->path,
                        'mime_type' => $image->mime_type,
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
            ->with('customerInfo') // customerInfo ilişkisini ekledik
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate(9);   

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
            ->with('customerInfo') // customerInfo ilişkisini ekledik
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate(5);   

        return response()->json(['orders' => $orders], 200);
    }
    

    
    
    public function downloadImage($imageId)
    {
    // Veritabanından ilgili imageId'ye sahip dosya bilgisini al
    $orderImage = OrderImage::findOrFail($imageId);

    // Dosyanın tam yolunu belirle
    $filePath = public_path(str_replace('public/', 'storage/', $orderImage->path));


    // Dosyanın boyutunu al
    $fileSize = filesize($filePath);

    // Dosyanın MIME tipini belirle
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $contentType = finfo_file($finfo, $filePath);
    finfo_close($finfo);

    // İndirme işlemi başladığında zamanı kaydet
    $startTime = microtime(true);

    // Dosyayı parça parça okuyup kullanıcıya gönder
    $response = new StreamedResponse(function () use ($filePath) {
        $handle = fopen($filePath, 'rb');
        while (!feof($handle)) {
            echo fread($handle, 1024);
            ob_flush();
            flush();
        }
        fclose($handle);
    }, 200, [
        'Content-Type' => $contentType,
        'Content-Length' => $fileSize, // Content-Length başlığını ayarla
        'Content-Disposition' => 'attachment; filename="' . $orderImage->path . '"'
    ]);

    // İndirme işlemi bittiğinde zamanı kaydet ve süreyi hesapla
    $endTime = microtime(true);
    $elapsedTime = $endTime - $startTime;

    // İndirme süresini yanıtın başına ekle
    $response->headers->add(['X-Elapsed-Time' => $elapsedTime]);

    return $response;
    }
    


    public function getManufacturerOrderHistory()
    {
        $manufacturerId = Auth::id();
    
        // Belirtilen üretici 'id' değerine sahip ve 'production_date' değeri null olmayan siparişleri al
        $orders = Order::where('manufacturer_id', $manufacturerId)
            ->whereNotNull('production_date')
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate(6);
    
        return response()->json(['order_history' => $orders], 200);
    }

    public function getCustomerOrderHistory()
    {
        $customerId = Auth::id();

        // Belirtilen müşteri 'id' değerine sahip ve 'production_date' değeri null olmayan siparişleri al
        $orders = Order::where('customer_id', $customerId)
            ->whereNotNull('production_date')
            ->with('customerInfo') // customerInfo ilişkisini ekledik
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate(6);

        return response()->json(['order_history' => $orders], 200);
    }
    

    /**
     * 'status' değeri 'PP' olan, 'estimated_production_date' değeri güncel tarih bilgisinden geri olan ve 'production_date' değeri <null>< /null> olan siparişleri getirir.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDelayedOrders()
    {
        // Belirtilen 'status' değeri 'PP' olan, 'estimated_production_date' değeri güncel tarih bilgisinden geri olan ve 'production_date' değeri null olan siparişleri al
        $orders = Order::where('status', 'PP')
            ->where('is_rejected', 'A')
            ->where('production_date', null)
            ->where('estimated_production_date', '<', now())
            ->with(['customer' => function ($query) {
                // İlgili müşteri ve üretici bilgilerini getir
                $query->select('user_id', 'id', 'name', 'surname', 'company_name', 'phone','image_url')
                ->with(['user' => function ($query) {
                    $query->select('id', 'email');
                }]);
            }, 'manufacturer' => function ($query) {
                // İlgili müşteri ve üretici bilgilerini getir
                $query->select('user_id','id', 'name', 'surname', 'company_name', 'phone')
                ->with(['user' => function ($query) {
                    $query->select('id', 'email');
                }]);
            }, 'customerInfo'])// customerInfo ilişkisini ekledik
            ->with([
                'rejects' => function ($query) {
                    // İlgili reject bilgilerini getir
                    $query->select('id', 'order_id', 'reason', 'created_at');
                },
            ]) 
            ->orderByDesc('updated_at') // En son güncellenenlere göre sırala
            ->paginate(9);  

        return response()->json(['orders' => $orders], 200);
    }

}

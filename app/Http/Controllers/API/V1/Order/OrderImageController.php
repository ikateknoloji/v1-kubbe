<?php

namespace App\Http\Controllers\API\V1\Order;

use App\Http\Controllers\Controller;
use App\Models\OrderImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderImageController extends Controller
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
            'order_id' => 'required|exists:orders,id',
            'type' => 'required|in:L,D,PR,I,PI,SC',
            'image_url' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    
        try {
            // Transaksiyon başlat
            DB::beginTransaction();
        
            // Resmi kaydet
            $image = $request->file('image_url');
            $imageName =  $request->input('type') . $request->input('order_id') . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('public/images/orders', $imageName);
        
            // Yeni sipariş resmi oluştur
            $orderImage = OrderImage::create([
                'order_id' => $request->input('order_id'),
                'type' => $request->input('type'),
                'image_url' => asset(Storage::url($path)),
                'path' => $path,
            ]);
        
            // Transaksiyonu tamamla
            DB::commit();
        
            // Başarılı oluşturma yanıtı
            return response()->json(['order_image' => $orderImage], 201);
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
    public function show(OrderImage $orderImage)
    {
        //
    }

    public function update(Request $request, OrderImage $orderImage)
    {

    }
 
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrderImage $orderImage)
    {
        try {
            // Transaksiyon başlat
            DB::beginTransaction();

            // Resmi sil
            Storage::disk('public')->delete($orderImage->path);

            // OrderImage kaydını sil
            $orderImage->delete();

            // Transaksiyonu tamamla
            DB::commit();

            // Başarılı silme yanıtı
            return response()->json(['message' => 'Sipariş resmi başarıyla silindi'], 200);
        } catch (\Exception $e) {
            // Hata durumunda transaksiyonu geri al
            DB::rollback();

            // Hata yanıtı
            return response()->json(['error' => 'İşlem sırasında bir hata oluştu.'], 500);
        }
    }

    /**
     * Update the image of the specified resource.
     */
    public function updateImage(Request $request, OrderImage $orderImage)
    {
        // Gelen verileri doğrula
        $request->validate([
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            // Transaksiyon başlat
            DB::beginTransaction();

            // Eski resmi sil
            Storage::disk('public')->delete($orderImage->path);

            // Yeni resmi kaydet (eğer varsa)
            if ($request->hasFile('image_url')) {
                $image = $request->file('image_url');
                $imageName = $orderImage->type .  $orderImage->order_id . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('public/images/orders', $imageName);

                // OrderImage bilgilerini güncelle
                $orderImage->update([
                    'image_url' => asset(Storage::url($path)),
                    'path' => $path,
                ]);
            }

            // Transaksiyonu tamamla
            DB::commit();

            // Başarılı güncelleme yanıtı
            return response()->json(['order_image' => $orderImage], 200);
        } catch (\Exception $e) {
            // Hata durumunda transaksiyonu geri al
            DB::rollback();

            // Hata yanıtı
            return response()->json(['error' => 'İşlem sırasında bir hata oluştu.'], 500);
        }
    }

}

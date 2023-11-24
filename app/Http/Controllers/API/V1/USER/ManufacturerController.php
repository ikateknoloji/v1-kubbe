<?php

namespace App\Http\Controllers\API\V1\USER;

use App\Http\Controllers\Controller;
use App\Models\Manufacturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ManufacturerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Veritabanından tüm üreticileri çek
            $manufacturers = Manufacturer::all();

            // Her üretici için getInfo metodunu çağır ve bilgileri al
            $manufacturerInfo = $manufacturers->map(function ($manufacturer) {
                return $manufacturer->getInfoAttribute();
            });

            // Başarılı yanıtı döndür
            return response()->json($manufacturerInfo, 200);
        } catch (\Exception $e) {
            // Hata durumunda hata yanıtını döndür
            return response()->json(['error' => 'İstek işlenirken bir hata oluştu. ' . $e], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Veritabanı işlemlerini transaksiyon içinde gerçekleştir
            DB::beginTransaction();

            // Gelen istek verilerini doğrula
            $validatedData = $request->validate([
                'name' => 'required|string',
                'surname' => 'required|string',
                'phone' => 'required|string',
                'tax_number' => 'required|string',
                'tax_office' => 'required|string',
                'company_name' => 'required|string',
                'address' => 'required|string',
                'city' => 'required|string',
                'district' => 'required|string',
                'country' => 'required|string',
                'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            // Giriş yapmış kullanıcının ID'sini verilere ekle
            $validatedData['user_id'] = Auth::id();

            // Eğer istekte bir resim varsa, Laravel'in depolama sistemini kullanarak kaydet
            if ($request->hasFile('image_url')) {
                $image = $request->file('image_url');
                $imageName = $validatedData['user_id'] . $image->getClientOriginalExtension();
                $path = $image->storeAs('public/images/profile', $imageName);
                $validatedData['path'] = $path;
                $validatedData['image_url'] = asset(Storage::url($path));
            }

            // Manufacturer modelini kullanarak veritabanına kaydet
            $manufacturer = Manufacturer::create($validatedData);

            // İşlem başarılı ise commit yap
            DB::commit();

            // Başarılı yanıtı döndür
            return response()->json(['message' => 'Üretici başarıyla oluşturuldu', 'manufacturer' => $manufacturer], 201);
        } catch (\Exception $e) {
            // Hata durumunda rollback yap
            DB::rollback();

            // Hata yanıtını döndür
            return response()->json(['error' => 'İstek işlenirken bir hata oluştu. ' . $e], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Manufacturer  $manufacturer
     * @return \Illuminate\Http\Response
     */
    public function show(Manufacturer $manufacturer)
    {
        try {
            // Üretici bilgilerini döndür
            return response()->json(['manufacturer' => $manufacturer], 200);
        } catch (\Exception $e) {
            // Hata durumunda yanıt döndür
            return response()->json(['error' => 'İstek işlenirken bir hata oluştu. ' . $e], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Manufacturer $manufacturer)
    {
        try {
            // Veritabanı işlemlerini transaksiyon içinde gerçekleştir
            DB::beginTransaction();
    
            // Gelen istek verilerini doğrula
            $validatedData = $request->validate([
                'name' => 'required|string',
                'surname' => 'required|string',
                'phone' => 'required|string',
                'tax_number' => 'required|string',
                'tax_office' => 'required|string',
                'company_name' => 'required|string',
                'address' => 'required|string',
                'city' => 'required|string',
                'district' => 'required|string',
                'country' => 'required|string',
            ]);
    
            // Üretici bilgilerini güncelle
            $manufacturer->update($validatedData);
    
            // İşlem başarılı ise commit yap
            DB::commit();
    
            // Başarılı yanıtı döndür
            return response()->json(['message' => 'Üretici başarıyla güncellendi', 'manufacturer' => $manufacturer], 200);
        } catch (\Exception $e) {
            // Hata durumunda rollback yap
            DB::rollback();
    
            // Hata yanıtını döndür
            return response()->json(['error' => 'İstek işlenirken bir hata oluştu. ' . $e], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Manufacturer  $manufacturer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Manufacturer $manufacturer)
    {
        try {
            // Veritabanı işlemlerini transaksiyon içinde gerçekleştir
            DB::beginTransaction();

            // Üreticinin resmini sil
            if ($manufacturer->path) {
                Storage::disk('public')->delete($manufacturer->path);
            }

            // Üreticiyi sil
            $manufacturer->delete();

            // İşlem başarılı ise commit yap
            DB::commit();

            // Başarılı yanıtı döndür
            return response()->json(['message' => 'Üretici başarıyla silindi'], 200);
        } catch (\Exception $e) {
            // Hata durumunda rollback yap
            DB::rollback();

            // Hata yanıtını döndür
            return response()->json(['error' => 'İstek işlenirken bir hata oluştu. ' . $e], 500);
        }
    }

    /**
     * Update the specified resource's image in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Manufacturer  $manufacturer
     * @return \Illuminate\Http\Response
     */
    public function updateImage(Request $request, Manufacturer $manufacturer)
    {
        try {
            // Veritabanı işlemlerini transaksiyon içinde gerçekleştir
            DB::beginTransaction();

            if ($request->hasFile('image_url')) {
                // Gelen istek verilerini doğrula
                $validatedData = $request->validate([
                    'image_url' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ]);

                // Eski resmi sil
                if ($manufacturer->path) {
                    Storage::disk('public')->delete($manufacturer->path);
                }

                // Yeni resmi kaydet
                $image = $request->file('image_url');
                $imageName = $manufacturer->user_id . $image->getClientOriginalExtension();
                $path = $image->storeAs('public/images/profile', $imageName);

                $manufacturer->path = $path;
                $manufacturer->image_url = asset(Storage::url($path));
            }

            // Üretici bilgilerini güncelle
            $manufacturer->save();

            // İşlem başarılı ise commit yap
            DB::commit();

            // Başarılı yanıtı döndür
            return response()->json(['message' => 'Üretici resmi başarıyla güncellendi', 'manufacturer' => $manufacturer], 200);
        } catch (\Exception $e) {
            // Hata durumunda rollback yap
            DB::rollback();

            // Hata yanıtını döndür
            return response()->json(['error' => 'İstek işlenirken bir hata oluştu. ' . $e], 500);
        }
    }

}

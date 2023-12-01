<?php

namespace App\Http\Controllers\API\V1\USER;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::all();

        // Her müşteri için getInfo metodunu çağır ve bilgileri al
        $customerInfo = $customers->map(function ($customer) {
            return $customer->getInfoAttribute();
        });

        return response()->json($customerInfo);
    }

    /**
     * Yeni bir kayıt oluşturur ve depolama işlemi yapar.
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
            
            $validatedData['user_id'] = Auth::id();

            if ($request->hasFile('image_url')) {
                $image = $request->file('image_url');
                $imageName = $validatedData['user_id'] .'.'. $image->getClientOriginalExtension();
                $path = $image->storeAs('public/images/profile', $imageName);
                $validatedData['path'] = $path;
                $validatedData['image_url'] = asset(Storage::url($path));
            }
            

            // Customer modelini kullanarak veritabanına kaydet
            $customer = Customer::create($validatedData);

            // İşlem başarılı ise commit yap
            DB::commit();

        
            return response()->json(['message' => 'Müşteri başarıyla oluşturuldu', 'result' => $customer], 201);
        } catch (\Exception $e) {
            // Hata durumunda rollback yap
            DB::rollback();

            // Hata yanıtını döndür
            return response()->json(['error' => 'İstek işlenirken bir hata oluştu. '. $e ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        // Müşteri bilgilerini döndür
        return response()->json($customer, 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
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

            // Müşteri bilgilerini güncelle
            $customer->update($validatedData);

            // İşlem başarılı ise commit yap
            DB::commit();

            return response()->json(['message' => 'Müşteri başarıyla güncellendi', 'customer' => $customer], 200);
        } catch (\Exception $e) {
            // Hata durumunda rollback yap
            DB::rollback();

            // Hata yanıtını döndür
            return response()->json(['error' => 'İstek işlenirken bir hata oluştu. '. $e ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        try {
            // Veritabanı işlemlerini transaksiyon içinde gerçekleştir
            DB::beginTransaction();

            // Müşterinin resmini sil
            if ($customer->path) {
                Storage::disk('public')->delete($customer->path);
            }

            // Müşteriyi sil
            $customer->delete();

            // İşlem başarılı ise commit yap
            DB::commit();

            return response()->json(['message' => 'Müşteri başarıyla silindi'], 200);
        } catch (\Exception $e) {
            // Hata durumunda rollback yap
            DB::rollback();

            // Hata yanıtını döndür
            return response()->json(['error' => 'İstek işlenirken bir hata oluştu. '. $e ], 500);
        }
    }

    /**
    * Update the specified resource's image in storage.
    */
    public function updateImage(Request $request, Customer $customer)
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
                if ($customer->path) {
                    Storage::disk('public')->delete($customer->path);
                }

                // Yeni resmi kaydet
                $image = $request->file('image_url');
                $imageName = $customer->user_id . $image->getClientOriginalExtension();
                $path = $image->storeAs('public/images/profile', $imageName);
                $customer->path = $path;
                $customer->image_url = asset(Storage::url($path));
            }

            // Müşteri bilgilerini güncelle
            $customer->save();

            // İşlem başarılı ise commit yap
            DB::commit();

            return response()->json(['message' => 'Müşteri resmi başarıyla güncellendi', 'customer' => $customer], 200);
        } catch (\Exception $e) {
            // Hata durumunda rollback yap
            DB::rollback();

            // Hata yanıtını döndür
            return response()->json(['error' => 'İstek işlenirken bir hata oluştu. '. $e ], 500);
        }
    }

}

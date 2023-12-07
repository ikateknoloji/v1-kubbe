<?php

namespace App\Http\Controllers\API\V1\Product;

use App\Http\Controllers\Controller;
use App\Models\ProductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductTypeController extends Controller
{
    /**
     * Ürün tiplerini listeler.
     */
    public function index()
    {
            $productTypes = ProductType::all();
            return response()->json(['productTypes' => $productTypes], 200);

    }

    /**
     * Yeni bir ürün tipi oluşturur ve kaydeder.
     */
    public function store(Request $request)
    {
        try {
            
            // Gelen verileri doğrulama
            $request->validate([
                'product_type' => 'required|unique:product_types,product_type',
                'product_category_id' => 'required|exists:product_categories,id',
                'image_url' => 'required|mimes:jpg,jpeg,png,gif',
            ], [
                'product_type.required' => 'Ürün tipi alanı gereklidir.',
                'product_type.unique' => 'Bu ürün tipi zaten kullanılmaktadır.',
                'product_category_id.required' => 'Ürün kategori ID alanı gereklidir.',
                'product_category_id.exists' => 'Geçerli bir ürün kategori ID seçmelisiniz.',
                'image_url.required' => 'Resim dosyası gereklidir.',
                'image_url.mimes' => 'Resim dosyası formatı jpg, jpeg, png veya gif olmalıdır.',
            ]);

            // Transaksiyon başlat
            DB::beginTransaction();

            // Yeni ürün tipini oluştur
            $productType = ProductType::create([
                'product_type' => $request->input('product_type'),
                'product_category_id' => $request->input('product_category_id'),
            ]);

            // Eğer resim dosyası varsa kaydet
            $this->handleImageUpload($request, $productType);

            // Transaksiyonu tamamla
            DB::commit();

            // Başarılı oluşturma yanıtı
            return response()->json(['productType' => $productType], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Belirtilen ürün tipini gösterir.
     */
    public function show(ProductType $productType)
    {
        try {
            // Ürün tipi detaylarını döndür
            return response()->json(['productType' => $productType], 200);
        } catch (\Exception $e) {
            // Hata durumunda uygun bir hata yanıtı döndür
            return response()->json(['error' => 'İşlem sırasında bir hata oluştu.'], 500);
        }
    }

    /**
     * Belirtilen ürün tipini günceller.
     */
    public function update(Request $request, ProductType $productType)
    {
        try {
            // Gelen verileri doğrulama
            $request->validate([
                'product_type' => 'required|unique:product_types,product_type,' . $productType->id,
                'product_category_id' => 'required|exists:product_categories,id',
                'image_url' => 'url',
            ], [
                'product_type.required' => 'Ürün tipi alanı gereklidir.',
                'product_type.unique' => 'Bu ürün tipi zaten kullanılmaktadır.',
                'product_category_id.required' => 'Ürün kategori ID alanı gereklidir.',
                'product_category_id.exists' => 'Geçerli bir ürün kategori ID seçmelisiniz.',
                'image_url.url' => 'Geçerli bir URL adresi girilmelidir.',
            ]);

            // Transaksiyon başlat
            DB::beginTransaction();

            // Ürün tipini güncelle
            $productType->update([
                'product_type' => $request->input('product_type'),
                'product_category_id' => $request->input('product_category_id'),
            ]);

            // Transaksiyonu tamamla
            DB::commit();

            // Başarılı güncelleme yanıtı
            return response()->json(['productType' => $productType], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Belirtilen ürün tipini siler.
     */
    public function destroy(ProductType $productType)
    {
        try {
            // Transaksiyon başlat
            DB::beginTransaction();

            // Eğer ürün tipine ait resim varsa sil
            $this->deleteImage($productType);

            // Ürün tipini sil
            $productType->delete();

            // Transaksiyonu tamamla
            DB::commit();

            // Başarılı silme yanıtı
            return response()->json(null, 204);
        } catch (\Exception $e) {
            // Hata durumunda transaksiyonu geri al
            DB::rollback();

            // Hata yanıtı
            return response()->json(['error' => 'İşlem sırasında bir hata oluştu.'], 500);
        }
    }

    /**
     * Ürün tipine ait resmi günceller.
     */
    public function updateImage(Request $request, ProductType $productType)
    {
        try {
            // Gelen verileri doğrulama
            $request->validate([
                'image_url' => 'required|mimes:jpg,jpeg,png,gif',
            ], [
                'image_url.required' => 'Resim dosyası gereklidir.',
                'image_url.mimes' => 'Resim dosyası formatı jpg, jpeg, png veya gif olmalıdır.',
            ]);

            // Transaksiyon başlat
            DB::beginTransaction();

            // Eğer ürün tipine ait eski resim varsa sil
            $this->deleteImage($productType);

            // Yeni resmi kaydet
            $this->handleImageUpload($request, $productType);

            // Transaksiyonu tamamla
            DB::commit();

            // Başarılı güncelleme yanıtı
            return response()->json(['productType' => $productType], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Ürün tipine ait resmi storage'dan siler.
     */
    private function deleteImage(ProductType $productType)
    {
        if ($productType->path) {
            Storage::delete($productType->path);
        }
    }

    /**
     * Ürün tipine ait resmi kaydedip günceller.
     */
    private function handleImageUpload(Request $request, ProductType $productType)
    {
        if ($request->hasFile('image_url')) {
            $image = $request->file('image_url');
            $imageName = "{$productType->id}.{$image->extension()}";
            $path = $image->storeAs('public/images/product_types', $imageName);

            $productType->update([
                'image_url' => asset(Storage::url($path)),
                'path' => $path,
            ]);
        }
    }
}

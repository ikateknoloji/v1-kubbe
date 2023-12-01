<?php

namespace App\Http\Controllers\API\V1\Product;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductCategoryController extends Controller
{
    /**
     * Kayıtlı kategorilerin listesini döndürür.
     */
    public function index()
    {
        $categories = ProductCategory::all();
        return response()->json(['categories' => $categories], 200);
    }
    /**
     * Yeni bir kategori oluşturur ve kaydeder.
     */
    public function store(Request $request)
    {
        // Gelen verileri doğrulama
        $request->validate([
            'category' => 'required|unique:product_categories,category',
            'image_url' => 'required|mimes:jpg,jpeg,png,gif',
        ]);
    
        try {
            // Transaksiyon başlat
            DB::beginTransaction();
    
            // Yeni kategori oluştur
            $category = ProductCategory::create([
                'category' => $request->input('category'),
            ]);
    
            if ($request->hasFile('image_url')) {
                $image = $request->file('image_url');
    
                $imageName = $category->id.$image->extension(); // veya başka bir uzantı
                $path  = $image->storeAs('public/images/categories', $imageName);
    
                // Kategoriyi güncelle, resim yolu ile birlikte
                $category->update(['path' => $path , 'image_url' => asset(Storage::url($path))]);
            }
    
            // Transaksiyonu tamamla
            DB::commit();
    
            // Başarılı oluşturma yanıtı
            return response()->json(['category' => $category], 201);
        } catch (\Exception $e) {
            // Hata durumunda transaksiyonu geri al
            DB::rollback();
    
            // Hata yanıtı
            return response()->json(['error' => 'İşlem sırasında bir hata oluştu.'], 500);
        }
    }
    


    /**
     * Belirtilen kategoriyi gösterir.
     */
    public function show(ProductCategory $productCategory)
    {
        try {
            // Kategori detaylarını döndür
            return response()->json(['category' => $productCategory], 200);
        } catch (\Exception $e) {
            // Hata durumunda uygun bir hata yanıtı döndür
            return response()->json(['error' => 'İşlem sırasında bir hata oluştu.'], 500);
        }
    }


    /**
     * Belirtilen kategoriyi günceller.
     */
    public function update(Request $request, ProductCategory $productCategory)
    {
        // Gelen verileri doğrulama
        $request->validate([
            'category' => 'required|unique:product_categories,category,' . $productCategory->id,
        ]);

        try {
            // Kategoriyi güncelle, sadece "category" alanını kullan
            $productCategory->update([
                'category' => $request->input('category'),
            ]);

            // Başarılı güncelleme yanıtı
            return response()->json(['category' => $productCategory], 200);
        } catch (\Exception $e) {
            // Hata durumunda uygun bir hata yanıtı döndür
            return response()->json(['error' => 'İşlem sırasında bir hata oluştu.'], 500);
        }
    }


    /**
     * Belirtilen kategoriyi siler.
     */
    public function destroy(ProductCategory $productCategory)
    {
        try {
            // Transaksiyon başlat
            DB::beginTransaction();

            // Kategoriye ait resmi kaldır
            if ($productCategory->path) {
                Storage::delete($productCategory->path);
            }

            // Kategoriyi sil
            $productCategory->delete();

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
     * Kategoriye ait resmi günceller.
     */
    public function updateImage(Request $request, ProductCategory $productCategory)
    {
        // Gelen verileri doğrulama
        $request->validate([
            'image_url' => 'required|mimes:jpg,jpeg,png,gif',
        ]);

        try {
            // Transaksiyon başlat
            DB::beginTransaction();

            // Kategoriye ait eski resmi kaldır
            if ($productCategory->path) {
                Storage::delete($productCategory->path);
            }

            // Yeni resmi storage'a kaydet
            $image = $request->file('image_url');
            $imageName = "{$productCategory->id}.{$image->extension()}"; // veya başka bir uzantı
            $path = $image->storeAs('public/images/categories', $imageName);

            // Kategoriyi güncelle, yeni resim yolu ile birlikte
            $productCategory->update([
                'path' => $path,
                'image_url' => asset(Storage::url($path)),
            ]);

            // Transaksiyonu tamamla
            DB::commit();

            // Başarılı güncelleme yanıtı
            return response()->json(['category' => $productCategory], 200);
        } catch (\Exception $e) {
            // Hata durumunda transaksiyonu geri al
            DB::rollback();

            // Hata yanıtı
            return response()->json(['error' => 'İşlem sırasında bir hata oluştu.'], 500);
        }
    }

}

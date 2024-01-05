<?php

use App\Http\Controllers\API\V1\AUTH\AuthController;
use App\Http\Controllers\API\V1\AUTH\PasswordResetController;
use App\Http\Controllers\API\V1\Manage\GetOrderController;
use App\Http\Controllers\API\V1\Manage\GetRejectOrderController;
use App\Http\Controllers\API\V1\Manage\OrderManageController;
use App\Http\Controllers\API\V1\Manage\RejectOrderController;
use App\Http\Controllers\API\V1\Order\NotificationController;
use App\Http\Controllers\API\V1\Order\OrderController;
use App\Http\Controllers\API\V1\Order\OrderImageController;
use App\Http\Controllers\API\V1\Order\OrderItemController;
use App\Http\Controllers\API\V1\Product\ProductCategoryController;
use App\Http\Controllers\API\V1\Product\ProductTypeController;
use App\Http\Controllers\API\V1\USER\CustomerController;
use App\Http\Controllers\API\V1\USER\ManufacturerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



// routes/web.php

Route::get('/download-image/{imageId}', [GetOrderController::class, 'downloadImage']);


/**
 * ? Kullanıcı giriş rotası.
 * TODO: Test amaçlı postman üzerinde istekler gerçekleştir
 */

// Kullanıcı girişi için rota
Route::post('/login', [AuthController::class, 'login']);
Route::post('/user-login', [AuthController::class, 'Userlogin']);

// Şifre sıfırlama için rota
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
    
Route::post('/broadcasting/auth', function (Request $request) {
  return Broadcast::auth($request);
});

Route::post('/check-token', [AuthController::class, 'checkToken']);


Route::middleware(['auth:sanctum'])->group(function () {

  Route::get('/customer/notifications', [NotificationController::class, 'getCustomerNotifications']);

  /**
    * ? şifre sıfırlama işlemlerinin yapıldığı rotalar.
    * TODO: Test amaçlı postman üzerinde istekler gerçekleştir
    */
  // Geçici şifre ile şifre sıfırlama için rota
  Route::post('/temp-password', [PasswordResetController::class, 'resetPasswordWithTempPassword']);

  Route::get('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Successfully logged out'
    ]);
  });


    
    // Kullanıcının şifresini güncelleme için rota
    Route::post('/update-password', [PasswordResetController::class, 'updatePassword']);


    /**
     * ? Sipariş oluşturma düzenleme gibi işlemlerin yapıldığı rota.
     * TODO: Test amaçlı postman üzerinde istekler gerçekleştir
     */

    // OrderController
    Route::apiResource('orders', OrderController::class);
    // OrderItemController Rotaları
    Route::apiResource('order-items', OrderItemController::class);
    // OrderImageController Rotaları
    Route::apiResource('order-images', OrderImageController::class);
    Route::post('/update-image/{orderImage}', [OrderImageController::class, 'updateImage']);


    Route::post('/validate-form', [OrderManageController::class, 'validateForms']);
    Route::post('/validate-order-item', [OrderManageController::class, 'validateOrderItem']);

    // Product Categories
    Route::group(['prefix' => 'order/category'], function () {
        // Ürün kategorileri için kaynak rotalarını tanımlar.
        Route::apiResource('product-categories', ProductCategoryController::class);
        
        Route::get('{categoryId}/types', [ProductTypeController::class, 'getProductTypesByCategoryId']);
    });
    
    Route::apiResource('customers', CustomerController::class);
    // TODO: Müşteri resmini güncellemek için özel bir rota.
    Route::post('info/{customer}/update-image', [CustomerController::class, 'updateImage']);

    /**
     * ? Admin kullanıcısı için oluşturulmuş korumalı rotalardır.
     * TODO: Sadece admin kullanıcısı ile işlemleri gerçekleştir.
     */

    Route::middleware(['user_permission:admin'])->group(function () {


        /**
        * ? Ürün tipleri ve Ürün kategorilerini yönetmek için kullandığımız rotalardır.
        * TODO: Sadece admin kullanıcısı ile işlemleri gerçekleştir. Ürün kategorisi oluştur.
        */




        
        // Product Types
        Route::group(['prefix' => 'products'], function () {
          // TODO: Ürün tipleri için kaynak rotalarını tanımlar.
          Route::apiResource('product-types', ProductTypeController::class);
          
            // TODO: Ürün tipi resmini güncellemek için özel bir rota.
            Route::post('{productType}/update-image', [ProductTypeController::class, 'updateImage']);
        });
        
        // Product Types
        Route::group(['prefix' => 'admin'], function () {
          Route::get('/notifications', [NotificationController::class, 'index']);
          Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
          Route::post('/notifications/read/all', [NotificationController::class, 'markAllAsRead']);
        });


        /**
         * ? Admin Customer ve Manufacturer kullanıcısı oluşturmak için korumalı rotalardır.
         * TODO: Sadece admin kullanıcısı ile işlemleri gerçekleştir. Admin oluştur.
          */

        Route::group(['prefix' => 'register'], function () {
            // Admin kaydı için özel bir rota.
            Route::post('admin', [AuthController::class, 'registerAdmin']);
        
            // Kullanıcı kaydı için özel bir rota.
            Route::post('user', [AuthController::class, 'registerUser']);
        });





        /**
         * ? Sipariş bilgilerinin servis rotası.
         * TODO: Sadece Üretici kullanıcısı ile işlemleri gerçekleştir.
         */
        Route::prefix('admin')->group(function () {
          Route::get('orders/active', [GetOrderController::class, 'getActiveOrders']);
          Route::get('orders/active/{status}', [GetOrderController::class, 'getOrdersByStatus']);
          Route::get('orders-item/active/{id}', [GetOrderController::class, 'getOrderById']);
          Route::get('manufacturers', [ManufacturerController::class, 'index']);
          Route::get('/oldest-orders', [GetOrderController::class, 'getOldestOrders']);

        });



        /**
         * ? İptal edilen sipariş bilgilerinin servis rotası.
         * TODO: Sadece Üretici kullanıcısı ile işlemleri gerçekleştir.
         */
        Route::prefix('admin')->group(function () {
          // Müşteri tarafından reddedilen siparişleri getirir.
          Route::get('rejected-orders', [GetRejectOrderController::class, 'getAdminRejectedOrders']);
          Route::get('canceled-orders', [GetRejectOrderController::class, 'getAdminCanceledOrders']);
        });


        /**
         * ? Şipariş durumu güncelleme.
         * TODO: Test et.
         */
        Route::prefix('admin/orders')->group(function () {
          // Sipariş durumunu tasarım aşamasına geçirme
          Route::post('/transition-to-design-phase/{order}', [OrderManageController::class, 'transitionToDesignPhase']);
          
          // Tasarımı onaylama ve resmi kaydetme 
          // TODO : get'e dönüştür
          Route::post('/approve-design/{order}', [OrderManageController::class, 'approveDesign']);
          
          // Ödemeyi doğrulama
          Route::post('/verify-payment/{order}', [OrderManageController::class, 'verifyPayment']);
          
          // Üretici seçimi işlemini gerçekleştirme
          Route::post('/select-manufacturer/{order}', [OrderManageController::class, 'selectManufacturer']);
          
          // Üretici Teklifi kabul
          Route::post('approve/{order}', [OrderManageController::class, 'offerApproveOrder']);

          // Ürünü kargo aşamasına işaretlenmiş olarak güncelleme
          Route::post('/mark-product-in-transition/{order}', [OrderManageController::class, 'markProductInTransition']);
          
          // Ürünü teslim edilmiş olarak işaretlenmiş olarak güncelleme
          Route::post('/mark-product-delivered/{order}', [OrderManageController::class, 'markProductDelivered']);

          // Fatura eklemek için rota
          Route::post('/order/{order}/add-invoice', [OrderManageController::class, 'addInvoice']);
        });
      



        /**
         * ? Admin tarafından şipariş reddetme ve iptal işlemlerinin gerçekleştirildiği yer.
         * TODO: Tüm rotaları test et ve edildiğine dair check işareti koy.
        */
        Route::group(['prefix' => 'manage'], function () {
            // TODO: Admin tarafından bir siparişi reddeder.
            Route::post('admin-reject-order/{orderId}', [RejectOrderController::class, 'adminRejectOrder']);
        
            // TODO: Bir siparişi iptal eder.
            Route::post('cancel-order/{orderId}', [RejectOrderController::class, 'cancelOrder']);

            Route::post('actived-order/{orderId}', [RejectOrderController::class, 'activateOrder']);

        });       
        
    

    });




    /**
     * ? Müşteri kullanıcısı için oluşturulmuş korumalı rotalardır.
     * TODO: Sadece müşteri kullanıcısı ile işlemleri gerçekleştir.
     */
    Route::middleware(['user_permission:customer'])->group(function () {
        
        Route::get('/customer-info', function (Request $request) {
          $user = $request->user();
          $user->customer;
      
          return $user;
        });


        /**
         * ? Müşteri bilgileri düzenleme resim ekleme veya güncelleme
         * TODO: Kullanıcı bilgileri ekle ve düzenle.
         */
/*
         Route::group(['prefix' => 'company'], function () {
          // TODO: Müşteri kaynak rotalarını tanımlar.
          Route::apiResource('customers', CustomerController::class);
      
          // TODO: Müşteri resmini güncellemek için özel bir rota.
          Route::post('{customer}/update-image', [CustomerController::class, 'updateImage']);
         });
*/
  

        /**
         * ?  Müşteri sipariş bilgilerinin servis rotası.
         * TODO: Sadece Üretici kullanıcısı ile işlemleri gerçekleştir.
         */
        Route::prefix('customer')->group(function () {
          Route::get('orders', [GetOrderController::class, 'getCustomerOrders']);
          Route::get('orders/{status}', [GetOrderController::class, 'getCustomerOrdersByStatus']);
          Route::get('orders-item/{id}', [GetOrderController::class, 'getOrderById']);
        });

        Route::post('customer/notifications/{id}/read', [NotificationController::class, 'markAsReadCustomer']);

        Route::put('/update-order/order-item/{id}', [OrderManageController::class, 'updateOrderItem']);

        /**
         * ? Şipariş durum bilgisini değiştirme.
         * TODO: Tüm testleri yap.
        */
        Route::prefix('customer/orders')->group(function () {
          // Tasarımı onaylama ve ödemeyi gerçekleştirme
          Route::post('/approve-payment-and-proceed/{order}', [OrderManageController::class, 'approvePaymentAndProceed']);

          // Ürünü teslim edilmiş olarak işaretlenmiş olarak güncelleme
          Route::post('/mark-product-delivered/{order}', [OrderManageController::class, 'markProductDelivered']);

          Route::post('/orders-image/{orderId}/update', [OrderController::class, 'updateLogo']);
        });


        /**
         * ? Şipariş üzerinde iptal talebi yade red durumları oluşturmak.
         * TODO: Tüm testleri yap.
        */

        Route::group(['prefix' => 'manage'], function () {
          // TODO: Müşteri tarafından bir siparişi reddeder.
          Route::post('/manage/customer-reject-order/{orderId}', [RejectOrderController::class, 'customerRejectOrder']);

          // TODO: Bir sipariş iptal talebi oluşturur.
          Route::post('/manage/cancel-order-request/{orderId}', [RejectOrderController::class, 'cancelOrderRequest']);
        });    

        /**
         * ? Şipariş üzerinde iptal ve reddedilenler.
         * TODO: Tüm testleri yap.
        */

        Route::group(['prefix' => 'customer'], function () {
          // TODO: Müşteri tarafından bir siparişi reddeder.
          Route::get('/canceled/orders', [GetRejectOrderController::class, 'getUserCanceledOrders']);

          // TODO: Bir sipariş iptal talebi oluşturur.
          Route::get('/rejected/orders', [GetRejectOrderController::class, 'getRejectedCustomerOrders']);
        }); 
    });
    



    /**
     * ? Üretici kullanıcısı için oluşturulmuş korumalı rotalardır.
     * TODO: Sadece Üretici kullanıcısı ile işlemleri gerçekleştir.
     */

    Route::middleware(['user_permission:manufacturer'])->group(function () {

      Route::get('/manufacturer-info', function (Request $request) {
        $user = $request->user();
        $user->manufacturer;
    
        return $user;
      });

        /**
         * ? Üretici bilgileri düzenleme resim ekleme veya güncelleme
         * TODO: Kullanıcı bilgileri ekle ve düzenle.
         */
         Route::group(['prefix' => 'company'], function () {
          // TODO: Üretici kaynak rotalarını tanımlar.
          Route::apiResource('manufacturers', ManufacturerController::class);
      
          // TODO: Üretici resmini güncellemek için özel bir rota.
          Route::post('info/{manufacturer}/update-image', [ManufacturerController::class, 'updateImage']);
         });
        

        /**
         * ? Üretici ile ilişkili şiparişleri servis eder.
         * TODO: Tüm testleri yap.
          */

        Route::prefix('manufacturer')->group(function () {
          Route::get('orders-item/active/{id}', [GetOrderController::class, 'getOrderById']);
          Route::get('orders', [GetOrderController::class, 'getManufacturerOrders']);
          Route::get('orders-manufacturer/{status}', [GetOrderController::class, 'getManufacturerOrdersByStatus']);
          Route::get('orders/{id}', [GetOrderController::class, 'getOrderById']);
        });



        /**
         * ? Üretici ile ilişkili reddedilen şiparişleri servis eder.
         * TODO: Tüm testleri yap.
          */

        Route::prefix('manufacturer')->group(function () {
          // Üretici tarafından reddedilen siparişleri getirir.
          Route::get('rejected-orders', [GetRejectOrderController::class, 'getRejectedManufacturerOrders']);
        });

        /**
         * ?  Siparişi onaylama.
         * TODO: Tüm testleri yap.
          */

        Route::prefix('manufacturer/orders')->group(function () {
          // Siparişi onaylama
          Route::post('/offer/{order}', [OrderManageController::class, 'offerManufacturer']);
          
          // Üretim sürecini başlatma rotası
          Route::get('/start-production/{order}', [OrderManageController::class, 'startProduction']);
          
          // Ürünü hazır olarak işaretlenmiş olarak güncelleme
          Route::put('/mark-product-ready/{order}', [OrderManageController::class, 'markProductReady']);
        });

        /**
         * ? Şiparişin reddetme ile ilgili olarak bir rota oluşturuldu.
         * TODO: Tüm testleri yap.
          */

        Route::group(['prefix' => 'manage'], function () {

          // TODO: Üretici tarafından bir siparişi reddeder.
          Route::post('manufacturer-reject-order/{orderId}', [RejectOrderController::class, 'manufacturerRejectOrder']);

          Route::get('manufacturer-reject-order/{orderId}', [RejectOrderController::class, 'manufacturerRejectOrder']);

        });



    });



    /**
    * TODO: Müşteri ve Üretici için tekil siparişin getirilmesiyle ilgili olarak tekil bir rota oluşturulacak.
    */

    // Belirtilen 'id' değerine sahip tekil siparişi getirir.
    Route::get('/manage/orders/{id}', [GetOrderController::class, 'getOrderById']);

});


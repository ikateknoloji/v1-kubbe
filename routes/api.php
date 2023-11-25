<?php

use App\Http\Controllers\API\V1\AUTH\AuthController;
use App\Http\Controllers\API\V1\Order\OrderController;
use App\Http\Controllers\API\V1\Order\OrderImageController;
use App\Http\Controllers\API\V1\Order\OrderItemController;
use App\Http\Controllers\API\V1\Product\ProductCategoryController;
use App\Http\Controllers\API\V1\Product\ProductTypeController;
use App\Http\Controllers\API\V1\USER\CustomerController;
use App\Http\Controllers\API\V1\USER\ManufacturerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

use App\Http\Middleware\UserPermission;

Route::post('/login', [AuthController::class, 'login']);


Route::middleware(['auth:sanctum'])->group(function () {

    Route::apiResource('manufacturers', ManufacturerController::class);
    Route::post('manufacturers/{manufacturer}/update-image', [ManufacturerController::class, 'updateImage']);

    Route::apiResource('customers', CustomerController::class);
    Route::post('customers/{customer}/update-image', [CustomerController::class, 'updateImage']);

    // Product Category Rotası
    Route::apiResource('product-categories', ProductCategoryController::class);
    Route::post('product-categories/{productCategory}/update-image', [ProductCategoryController::class, 'updateImage']);

    // Product Type Rotası
    Route::apiResource('product-types', ProductTypeController::class);
    Route::post('product-types/{productType}/update-image', [ProductTypeController::class, 'updateImage']);
    
    
    Route::apiResource('orders', OrderController::class);

    // OrderItemController Rotaları
    Route::apiResource('order-items', OrderItemController::class);

    // OrderImageController Rotaları
    Route::apiResource('order-images', OrderImageController::class);


    Route::middleware(['user_permission:admin'])->group(function () {
        // Admin rotaları...
    });

    Route::middleware(['user_permission:customer'])->group(function () {
        // Customer rotaları...
    });

    Route::middleware(['user_permission:manufacturer'])->group(function () {
        // Manufacturer rotaları...
    });
});


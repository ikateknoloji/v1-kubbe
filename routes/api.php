<?php

use App\Http\Controllers\API\V1\AUTH\AuthController;
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
    Route::apiResource('customers', CustomerController::class);

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


<?php

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

Route::middleware(['auth:sanctum', UserPermission::class])->group(function () {
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


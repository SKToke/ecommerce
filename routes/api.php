<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    /* Product */
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'create']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'delete']);

    /* Order */
    Route::post('/orders', [OrderController::class, 'create']);
    Route::put('/orders/{order}', [OrderController::class, 'update']);
});


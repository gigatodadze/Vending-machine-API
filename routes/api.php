<?php

use Illuminate\Http\Request;
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

Route::group(['middleware' => ['auth:sanctum']], function() {
    Route::post('buy', [\App\Http\Controllers\ProductController::class,'buy']);
});

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('products', \App\Http\Controllers\ProductController::class);


//Route::post('buy', function($userId,$productId)
//{
//    return $userOrders = \App\Http\Controllers\ProductController::buy($userId,$productId);
//
//});


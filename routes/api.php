<?php

use App\Http\Controllers\Api\AuthControllerApi;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\CatalogControllerApi;
use App\Http\Controllers\Api\ContactUsControllerApi;
use App\Http\Controllers\Api\MasterBahanControllerApi;
use App\Http\Controllers\Api\MasterJenisKatalogControllerApi;
use App\Http\Controllers\Api\OrderControllerApi;
use App\Http\Controllers\Api\UserControllerApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;   

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/auth/register', [AuthControllerApi::class, 'register']);
Route::post('/auth/login', [AuthControllerApi::class, 'login']);
// Route::get('/test', function() {
//     return response()->json(['message' => 'API is working']);
// });
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/auth/logout', [AuthControllerApi::class, 'logout']);
    Route::get('/auth/whoami', [AuthControllerApi::class, 'whoami']);

    // data master bahan
    Route::group(['prefix' => 'master_bahan'], function () {
        Route::get('/', [MasterBahanControllerApi::class, 'index']);
        Route::post('/store', [MasterBahanControllerApi::class, 'store']);
        Route::get('/show/{id}', [MasterBahanControllerApi::class, 'show']);
        Route::post('/update/{id}', [MasterBahanControllerApi::class, 'update']);
        Route::delete('/delete/{id}', [MasterBahanControllerApi::class, 'destroy']);
    });
    // data master jenis katalog
    Route::group(['prefix' => 'master_jenis'], function () {
        Route::get('/', [MasterJenisKatalogControllerApi::class, 'index']);
        Route::post('/store', [MasterJenisKatalogControllerApi::class, 'store']);
        Route::get('/show/{id}', [MasterJenisKatalogControllerApi::class, 'show']);
        Route::post('/update/{id}', [MasterJenisKatalogControllerApi::class, 'update']);
        Route::delete('/delete/{id}', [MasterJenisKatalogControllerApi::class, 'destroy']);
    });

    Route::group(['prefix' => 'users'], function () {
        Route::get('/', [UserControllerApi::class, 'index']);
        Route::post('/store', [UserControllerApi::class, 'store']);
        Route::get('/show/{id}', [UserControllerApi::class, 'show']);
        Route::post('/update/{id}', [UserControllerApi::class, 'update']);
        Route::delete('/delete/{id}', [UserControllerApi::class, 'destroy']);
    });
});


Route::prefix('/catalog')->group(function () {
    // Add your catalog routes here
    Route::get('/', [CatalogControllerApi::class, 'index']);
    Route::get('/show/{id}', [CatalogControllerApi::class, 'show']);
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/store', [CatalogControllerApi::class, 'store']);
        Route::post('/addStock/{id}', [CatalogControllerApi::class, 'addStock']);
        Route::post('/update/{id}', [CatalogControllerApi::class, 'update']);
        Route::delete('/delete/{id}', [CatalogControllerApi::class, 'destroy']);
    }
        
    );

});

Route::prefix('/contactus')->group(function () {
    // Add your contact us routes here
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [ContactUsControllerApi::class, 'index']);
        Route::get('/show/{id}', [ContactUsControllerApi::class, 'show']);
        Route::delete('/delete/{id}', [ContactUsControllerApi::class, 'destroy']);
    });

    Route::post('/send', [ContactUsControllerApi::class, 'store']);
});

Route::prefix('/order')->group(function () {
    // Add your order routes here
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/itemlist', [OrderControllerApi::class, 'CartIndex']);
        Route::post('/additem', [OrderControllerApi::class, 'addCart']);
    });
});
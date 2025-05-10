<?php

use App\Http\Controllers\Api\Admin\CartControllerApi;
use App\Http\Controllers\Api\Admin\CatalogPOSControllerApi;
use App\Http\Controllers\Api\AuthControllerApi;
use App\Http\Controllers\Api\CatalogControllerApi;
use App\Http\Controllers\Api\ContactUsControllerApi;
use App\Http\Controllers\Api\CustomOrderControllerApi;
use App\Http\Controllers\Api\HistoryControllerApi;
use App\Http\Controllers\Api\KeuanganControllerApi;
use App\Http\Controllers\Api\MasterBahanControllerApi;
use App\Http\Controllers\Api\MasterJenisKatalogControllerApi;
use App\Http\Controllers\Api\OrderControllerApi;
use App\Http\Controllers\Api\ProfileControllerApi;
use App\Http\Controllers\Api\ReviewControllerApi;
use App\Http\Controllers\Api\UserControllerApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;   

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/master_jenis/', [MasterJenisKatalogControllerApi::class, 'index']);
Route::get('/master_jenis/{id}', [MasterJenisKatalogControllerApi::class, 'show']);


Route::post('/auth/register', [AuthControllerApi::class, 'register']);
Route::post('/auth/login', [AuthControllerApi::class, 'login']);

Route::post('/track-visitor', [UserControllerApi::class, 'visitorStore']);

// Profile route 
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/auth/logout', [AuthControllerApi::class, 'logout']);
    Route::get('/auth/me', [ProfileControllerApi::class, 'me']);

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
        Route::post('/store', [MasterJenisKatalogControllerApi::class, 'store']);
        Route::post('/update/{id}', [MasterJenisKatalogControllerApi::class, 'update']);
        Route::delete('/delete/{id}', [MasterJenisKatalogControllerApi::class, 'destroy']);
    });

    Route::group(['prefix' => 'users'], function () {
        Route::get('/', [UserControllerApi::class, 'index']);
        Route::post('/store', [UserControllerApi::class, 'store']);
        Route::get('/show/{id}', [UserControllerApi::class, 'show']);
        Route::post('/update/{id}', [UserControllerApi::class, 'update']);
        Route::delete('/delete/{id}', [UserControllerApi::class, 'destroy']);

        Route::get('/count', [UserControllerApi::class, 'countUser']);
        Route::get('/count/visitor', [UserControllerApi::class, 'VisitorCount']);
    });

    Route::group(['prefix'=>'profile'], function(){
        Route::get('/', [ProfileControllerApi::class, 'me']);
        Route::post('/update_profile', [ProfileControllerApi::class, 'updateProfile']);
        Route::post('/update_avatar', [ProfileControllerApi::class, 'updateProfilePhoto']);
        Route::post('/reset_password', [ProfileControllerApi::class, 'linkResetPassword']);
        Route::post('/update_password', [ProfileControllerApi::class, 'updatePassword']);
        Route::post('/change_password', [ProfileControllerApi::class, 'changePassword']);
    });


    Route::group(['prefix'=>'history'], function(){
        Route::get('/', [HistoryControllerApi::class, 'index']);
        Route::get('/revenue', [HistoryControllerApi::class, 'dailyRevenue']);
        Route::get('/routine', [HistoryControllerApi::class, 'activitySummary']);
        Route::get('/admin', [HistoryControllerApi::class, 'adminActivity']);
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
        Route::get('/', [OrderControllerApi::class, 'index']);
        Route::get('/show/{id}', [OrderControllerApi::class, '  ']);
        Route::get('/itemlist', [OrderControllerApi::class, 'CartIndex']);
        Route::post('/additem', [OrderControllerApi::class, 'addCart']);
        Route::delete('/removeItem', [OrderControllerApi::class, 'removeItems']);
        Route::post('/checkout', [OrderControllerApi::class, 'checkout']);
        Route::post('/checkout/buktibayar', [OrderControllerApi::class, 'uploadPaymentProof']);
        Route::post('/admin/verif/{id}', [OrderControllerApi::class, 'AdminVerifPayment']);
        Route::get('/history', [OrderControllerApi::class, 'getMyOrders']);

        Route::get('/monthly', [OrderControllerApi::class, 'getMonthly']);

        Route::get('/tracking', [OrderControllerApi::class, 'getOrderHaventDone']);
        Route::get('/all', [OrderControllerApi::class, 'getAllCustonAndOrder']);
        Route::post('/sendToDelivery/{id}', [OrderControllerApi::class, 'sendToDelivery']);
        Route::get('/deliveryStatus', [OrderControllerApi::class, 'getOrdersWithDeliveryStatus']);
        Route::post('/recieved/{id}', [OrderControllerApi::class, 'shipOrder']);
        Route::post('/complete/{id}', [OrderControllerApi::class, 'completeOrder']);
        
        Route::prefix('/custom')->group(function(){
            Route::get('/', [CustomOrderControllerApi::class, 'index']);
            Route::get('/show/{id}', [CustomOrderControllerApi::class, 'show']);
            Route::post('/propose', [CustomOrderControllerApi::class, 'propose']);
            Route::post('/accept/propose', [CustomOrderControllerApi::class, 'acceptPropose']);
            Route::post('/finalize/{id}', [CustomOrderControllerApi::class, 'updateStatus']);
        });

       
        // Route::post('/addReviews/{id}', [OrderControllerApi::class, 'Reviews']);
        // Route::get('/reviews', [OrderControllerApi::class, 'getReviews']);
    });
});
Route::prefix('/reviews')->group(function () {
    Route::get('/', [ReviewControllerApi::class, 'getAllReviews']);
    Route::get('/detail/{id}', [ReviewControllerApi::class, 'detailReviews']);
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/addReviews/{id}', [ReviewControllerApi::class, 'addReviews']);
        Route::post('/reply/{id}', [ReviewControllerApi::class, 'replyReviews']);   
    });

});
Route::prefix('/cashier')->group(function () {
    // Add your cashier routes here
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('catalog')->group(function () {
            Route::get('/', [CatalogPOSControllerApi::class, 'index']);
            Route::get('/show/{id}', [CatalogPOSControllerApi::class, 'show']);
            Route::post('/add-cart', [CatalogPOSControllerApi::class, 'addCart']);
        });

        Route::prefix('order/cart')->group(function () {
            Route::get('/', [CartControllerApi::class, 'index']);
            Route::get('/show/{id}', [CartControllerApi::class, 'show']);
            Route::post('/update/{id}', [CartControllerApi::class, 'update']);
            Route::delete('/delete/{id}', [CartControllerApi::class, 'destroy']);
        });
      
    });
});

Route::prefix('/keuangan')->group(function() {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [KeuanganControllerApi::class, 'index']);
        Route::get('/ProfitLossMonthly', [KeuanganControllerApi::class, 'trackMonthlyIncomeProfitLoss']);
        Route::get('/revenue', [KeuanganControllerApi::class, 'dailyRevenue']);
        Route::get('/routine', [KeuanganControllerApi::class, 'activitySummary']);
        Route::get('/admin', [KeuanganControllerApi::class, 'adminActivity']);
    });
});
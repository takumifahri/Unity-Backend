<?php

use App\Http\Controllers\Api\AuthControllerApi;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

    // Google OAuth Routes
    Route::get('/auth/google', [AuthControllerApi::class, 'redirectToGoogle']);
    Route::get('/auth/google/callback', [AuthControllerAPI::class, 'handleGoogleCallback']);

    // Google OAuth Routes
    Route::get('/auth/facebook', [AuthControllerApi::class, 'redirectToFacebook']);
    Route::get('/auth/facebook/callback', [AuthControllerAPI::class, 'handleFacebookCallback']);


require __DIR__.'/auth.php';

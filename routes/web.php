<?php

use App\Http\Controllers\Api\AuthControllerApi;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

    // Google OAuth Routes
    Route::get('/auth/google', [AuthControllerApi::class, 'redirectToGoogle']);
    Route::get('/auth/google/callback', [AuthControllerAPI::class, 'handleGoogleCallback']);

    // Google OAuth Routes
    Route::get('/auth/facebook', [AuthControllerApi::class, 'redirectToFacebook']);
    Route::get('/auth/facebook/callback', [AuthControllerAPI::class, 'handleFacebookCallback']);

    Route::get('/download-image', function (Request $request) {
        $path = $request->input('path');
        $filename = $request->input('filename', 'downloaded-image');
        
        // Validate path to prevent directory traversal
        if (!Storage::exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }
        
        // Get file contents
        $file = Storage::get($path);
        $type = Storage::mimeType($path);
        
        // Create response with file
        $response = response($file, 200);
        $response->header('Content-Type', $type);
        $response->header('Content-Disposition', 'attachment; filename="' . $filename . '.jpg"');
        
        // Add CORS headers
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', 'GET');
        $response->header('Access-Control-Allow-Headers', 'Content-Type');
        
        return $response;
    });
require __DIR__.'/auth.php';

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

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

// API v1 Routes
Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/register', [\App\Http\Controllers\Api\V1\AuthController::class, 'register']);
    Route::post('/login', [\App\Http\Controllers\Api\V1\AuthController::class, 'login']);
    Route::post('/login/google', [\App\Http\Controllers\Api\V1\AuthController::class, 'googleLogin']);
    Route::post('/refresh-token', [\App\Http\Controllers\Api\V1\AuthController::class, 'refreshToken']);

    // Protected routes
    Route::middleware(['auth:sanctum', 'verify.device'])->group(function () {
        Route::get('/user', [\App\Http\Controllers\Api\V1\AuthController::class, 'user']);
        Route::post('/logout', [\App\Http\Controllers\Api\V1\AuthController::class, 'logout']);
        
        // Products
        Route::get('/products', [\App\Http\Controllers\Api\V1\ProductController::class, 'index']);
        Route::get('/products/{product}', [\App\Http\Controllers\Api\V1\ProductController::class, 'show']);
        Route::post('/products', [\App\Http\Controllers\Api\V1\ProductController::class, 'store']);
        Route::put('/products/{product}', [\App\Http\Controllers\Api\V1\ProductController::class, 'update']);
        Route::delete('/products/{product}', [\App\Http\Controllers\Api\V1\ProductController::class, 'destroy']);

        // Orders (Checkout)
        Route::get('/orders', [\App\Http\Controllers\Api\V1\OrderController::class, 'index']);
        Route::get('/orders/{order}', [\App\Http\Controllers\Api\V1\OrderController::class, 'show']);
        Route::post('/orders', [\App\Http\Controllers\Api\V1\OrderController::class, 'store']);
        Route::put('/orders/{order}', [\App\Http\Controllers\Api\V1\OrderController::class, 'update']);
        Route::delete('/orders/{order}', [\App\Http\Controllers\Api\V1\OrderController::class, 'destroy']);

        // Vendor Orders (alias of orders with vendor filtering)
        Route::get('/vendor-orders', [\App\Http\Controllers\Api\V1\OrderController::class, 'index']);
        Route::put('/vendor-orders/{order}', [\App\Http\Controllers\Api\V1\OrderController::class, 'update']);

        // Wishlist
        Route::get('/wishlist', [\App\Http\Controllers\Api\V1\WishlistController::class, 'index']);
        Route::post('/wishlist/{product}', [\App\Http\Controllers\Api\V1\WishlistController::class, 'store']);
        Route::delete('/wishlist/{product}', [\App\Http\Controllers\Api\V1\WishlistController::class, 'destroy']);
        
        // Vendor Details
        Route::get('/vendor-details', [\App\Http\Controllers\Api\V1\VendorController::class, 'getVendorDetails']);
        Route::post('/vendor-details', [\App\Http\Controllers\Api\V1\VendorController::class, 'submitVendorRequest']);
        Route::put('/vendor-requests/{vendorRequest}/status', [\App\Http\Controllers\Api\V1\VendorController::class, 'updateVendorRequestStatus']);

        // User profile & password
        Route::put('/user/profile', [\App\Http\Controllers\Api\V1\UserController::class, 'updateProfile']);
        Route::post('/user/avatar', [\App\Http\Controllers\Api\V1\UserController::class, 'uploadAvatar']);
        Route::put('/user/password', [\App\Http\Controllers\Api\V1\UserController::class, 'changePassword']);
    });

    // Forgot password (public)
    Route::post('/forgot-password', function (\Illuminate\Http\Request $request) {
        $request->validate(['email' => ['required','email']]);
        $status = \Illuminate\Support\Facades\Password::sendResetLink($request->only('email'));
        return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
            ? response()->json(['status' => 'success','message' => __($status)])
            : response()->json(['status' => 'error','message' => __($status)], 422);
    });
 

 });

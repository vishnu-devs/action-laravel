<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Web\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Web\Admin\RolePermissionController;
use App\Http\Controllers\Web\Admin\UserController;
use App\Http\Controllers\Web\Admin\VendorRequestController as AdminVendorRequestController;
use App\Http\Controllers\Web\VendorRequestController as WebVendorRequestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Email verification routes
Route::middleware('auth')->group(function () {
    Route::get('/verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');
});

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Products routes (admin only)
Route::middleware(['auth', 'role:admin|super_admin'])->group(function () {
    Route::get('/admin/products', [App\Http\Controllers\Web\Admin\ProductController::class, 'index'])->name('admin.products.index');
});

// Orders routes
Route::middleware(['auth', 'role:admin|super_admin'])->group(function () {
    Route::get('/admin/orders', [App\Http\Controllers\Web\Admin\OrderController::class, 'index'])->name('admin.orders.index');
});

// Vendor request routes (for customers)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('vendor-request/create', [WebVendorRequestController::class, 'create'])->name('vendor-request.create');
    Route::post('vendor-request', [WebVendorRequestController::class, 'store'])->name('vendor-request.store');
});

// Admin routes
Route::middleware(['auth', 'role:admin|super_admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin dashboard
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // User management
    Route::resource('users', UserController::class);
    
    // Vendor requests management
    Route::resource('vendor-requests', AdminVendorRequestController::class)->only(['index', 'show', 'update']);
});

// Super admin routes
Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    // Roles and permissions management
    Route::get('roles-permissions', [RolePermissionController::class, 'index'])->name('roles-permissions.index');
    Route::post('roles', [RolePermissionController::class, 'createRole'])->name('roles-permissions.create-role');
    Route::post('permissions', [RolePermissionController::class, 'createPermission'])->name('roles-permissions.create-permission');
    Route::post('assign-role', [RolePermissionController::class, 'assignRole'])->name('roles-permissions.assign-role');
    Route::post('remove-role', [RolePermissionController::class, 'removeRole'])->name('roles-permissions.remove-role');
    Route::post('assign-permission', [RolePermissionController::class, 'assignPermission'])->name('roles-permissions.assign-permission');
    Route::post('remove-permission', [RolePermissionController::class, 'removePermission'])->name('roles-permissions.remove-permission');
});

require __DIR__.'/auth.php';

// General products and orders routes mapped to Blade views
Route::middleware(['auth'])->group(function () {
    // Products listing for authenticated users (vendors see their own)
    Route::get('/products', [App\Http\Controllers\Web\ProductController::class, 'index'])->name('products.index');

    // Product management (vendors/admins only)
    Route::middleware(['role:vendor|admin|super_admin'])->group(function () {
        Route::get('/products/create', [App\Http\Controllers\Web\ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [App\Http\Controllers\Web\ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [App\Http\Controllers\Web\ProductController::class, 'edit'])->name('products.edit');
        Route::patch('/products/{product}', [App\Http\Controllers\Web\ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [App\Http\Controllers\Web\ProductController::class, 'destroy'])->name('products.destroy');
    });

    // Orders listing for authenticated users (role-based filtering in controller)
    Route::get('/orders', [App\Http\Controllers\Web\OrderController::class, 'index'])->name('orders.index');
    // Vendor order status edit/update
    Route::middleware(['role:vendor'])->group(function () {
        Route::get('/orders/{order}/edit', [App\Http\Controllers\Web\OrderController::class, 'edit'])->name('orders.edit');
        Route::patch('/orders/{order}', [App\Http\Controllers\Web\OrderController::class, 'update'])->name('orders.update');
    });
});

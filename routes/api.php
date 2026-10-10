<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfitLossController;
use App\Http\Controllers\Api\StockInController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Public Routes (Tidak Membutuhkan Token)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Membutuhkan Bearer Token Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth & Profile
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Master Data Kategori (CRUD)
    Route::apiResource('categories', CategoryController::class);

    // Master Data Produk (CRUD)
    Route::apiResource('products', ProductController::class);

    // Transaksi Kasir & Inventaris
    Route::get('/transactions', [TransactionController::class, 'index']);      // Riwayat Transaksi
    Route::post('/transactions', [TransactionController::class, 'store']);     // Proses Checkout
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);  // Detail Nota/Struk
    Route::post('/transactions/{id}/cancel', [TransactionController::class, 'cancel']);
    Route::post('/transactions/{id}/restore', [TransactionController::class, 'restore']);
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);

    // Dashboard Stats
    Route::get('/dashboard-stats', [TransactionController::class, 'dashboardStats']);

    // Stok Masuk / Restock
    Route::get('/stock-ins', [StockInController::class, 'index']);
    Route::post('/stock-ins', [StockInController::class, 'store']);

    // Laporan Laba / Rugi
    Route::get('/reports/profit-loss', [ProfitLossController::class, 'getReport']);

    // User Management (CRUD)
    Route::apiResource('users', UserController::class);

    // Setting Toko
    Route::get('/settings', [\App\Http\Controllers\Api\SettingController::class, 'index']);
    Route::post('/settings', [\App\Http\Controllers\Api\SettingController::class, 'update']);
});

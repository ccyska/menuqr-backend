<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\MenuVariantController;
use App\Http\Controllers\MenuAddonController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\ReportController;


// ====================
// AUTH
// ====================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// ====================
// PUBLIC
// ====================

// Restaurant
Route::get('/restaurants', [RestaurantController::class, 'index']);
Route::get('/restaurants/{slug}/menu', [RestaurantController::class, 'menu']);

// Category
Route::get('/categories', [CategoryController::class, 'index']);

// Menu
Route::get('/menus', [MenuController::class, 'index']);
Route::get('/menus/{id}', [MenuController::class, 'show']);

// Variant & Addon untuk customer
Route::get('/menus/{menuId}/variants', [MenuVariantController::class, 'index']);
Route::get('/menus/{menuId}/addons', [MenuAddonController::class, 'index']);

// Validasi meja dari QR
Route::get('/restaurants/{slug}/tables/{code}', [TableController::class, 'validateTable']);

// Order customer
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{orderCode}', [OrderController::class, 'show']);
Route::get('/orders/{orderCode}/whatsapp', [OrderController::class, 'whatsapp']);


// ====================
// ADMIN
// ====================

Route::middleware('auth:sanctum')->group(function () {

    // ====================
    // AUTH
    // ====================

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/admin/categories', [CategoryController::class, 'adminIndex']);


    // ====================
    // RESTAURANT
    // ====================

    Route::post('/restaurants', [RestaurantController::class, 'store']);
    Route::put('/restaurants/{id}', [RestaurantController::class, 'update']);
    Route::delete('/restaurants/{id}', [RestaurantController::class, 'destroy']);


    // ====================
    // CATEGORY
    // ====================

    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);


    // ====================
    // MENU
    // ====================

    Route::post('/menus', [MenuController::class, 'store']);
    Route::put('/menus/{id}', [MenuController::class, 'update']);
    Route::delete('/menus/{id}', [MenuController::class, 'destroy']);
    Route::get('/admin/menus', [MenuController::class, 'adminIndex']);


    // ====================
    // VARIANT
    // ====================

    Route::post('/menus/{menuId}/variants', [MenuVariantController::class, 'store']);
    Route::put('/variants/{id}', [MenuVariantController::class, 'update']);
    Route::delete('/variants/{id}', [MenuVariantController::class, 'destroy']);


    // ====================
    // ADDON / TOPPING
    // ====================

    Route::post('/menus/{menuId}/addons', [MenuAddonController::class, 'store']);
    Route::put('/addons/{id}', [MenuAddonController::class, 'update']);
    Route::delete('/addons/{id}', [MenuAddonController::class, 'destroy']);


    // ====================
    // TABLE / MEJA
    // ====================

    Route::get('/tables', [TableController::class, 'index']);
    Route::post('/tables', [TableController::class, 'store']);
    Route::put('/tables/{id}', [TableController::class, 'update']);
    Route::delete('/tables/{id}', [TableController::class, 'destroy']);


    // ====================
    // QR CODE
    // ====================

    Route::get('/tables/{id}/qr', [TableController::class, 'generateQr']);
    Route::get('/tables/{id}/qr/download', [TableController::class, 'downloadQr']);


    // ====================
    // PROMO
    // ====================

    Route::get('/promos', [PromoController::class, 'index']);
    Route::post('/promos', [PromoController::class, 'store']);
    Route::put('/promos/{id}', [PromoController::class, 'update']);
    Route::delete('/promos/{id}', [PromoController::class, 'destroy']);


    // ====================
    // ORDERS
    // ====================

    Route::get('/orders', [OrderController::class, 'index']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);


 // ====================
// DASHBOARD & REPORT
// ====================

Route::get('/dashboard', [ReportController::class, 'dashboard']);
Route::get('/reports/sales', [ReportController::class, 'sales']);
Route::get('/reports/sales/export', [ReportController::class, 'exportExcel']);
Route::get('/reports/sales/export/pdf', [ReportController::class, 'exportPdf']);
});